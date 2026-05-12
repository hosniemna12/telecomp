<?php

namespace App\Services\Orchestration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\TcFichier;
use App\Models\TcEnrGlobal;
use App\Models\TcEnrDetail;
use App\Models\TcRejet;
use App\Models\TcXmlProduit;
use App\Contracts\ParserInterface;
use App\Contracts\ValidatorInterface;
use App\Contracts\TransformerInterface;
use Illuminate\Support\Facades\Auth;
use App\Models\TcNotification;
use App\Models\User;
use App\Services\Audit\LogService;
use App\Services\Ml\MlScoringService;
use App\Services\Persistence\FichierPersistenceService;
use App\Services\Notification\NotificationDispatchService;

class FichierTraitementService
{
    protected ParserInterface      $parser;
    protected ValidatorInterface   $validator;
    protected TransformerInterface $transformer;
    protected LogService           $logService;
    protected MlScoringService    $mlScoring;
    protected FichierPersistenceService $persistence;
    protected NotificationDispatchService $notifier;

    public function __construct(
        ParserInterface      $parser,
        ValidatorInterface   $validator,
        TransformerInterface $transformer,
        LogService           $logService,
        MlScoringService     $mlScoring,
        FichierPersistenceService $persistence,
        NotificationDispatchService $notifier
    ) {
        $this->parser      = $parser;
        $this->validator   = $validator;
        $this->transformer = $transformer;
        $this->logService  = $logService;
        $this->mlScoring   = $mlScoring;
        $this->persistence = $persistence;
        $this->notifier    = $notifier;
    }

    // ──────────────────────────────────────────────────────────────
    // PIPELINE PRINCIPAL
    // ──────────────────────────────────────────────────────────────
    public function traiter(string $cheminFichier, string $typeForce = ''): array
    {
        $resultat = [
            'succes'     => false,
            'fichier_id' => null,
            'message'    => '',
            'stats'      => [],
            'ml'         => [],
        ];

        DB::beginTransaction();

        try {
            // 1. Créer l'entrée fichier
            $fichier = TcFichier::create([
                'nom_fichier'         => basename($cheminFichier),
                'chemin_complet'      => $cheminFichier,
                'type_valeur'         => $typeForce ?: '10',
                'code_enregistrement' => '21',
                'statut'              => 'EN_COURS',
                'date_reception'      => now(),
            ]);

            $this->log('info', "Fichier reçu : {$fichier->nom_fichier}", [
                'fichier_id' => $fichier->id,
                'etape'      => 'ACQUISITION',
            ]);

            // 2. Parser le fichier
            $donnees = $this->parser->parse($cheminFichier);

            if ($typeForce) {
                $donnees['type_valeur'] = $typeForce;
                foreach ($donnees['details'] as &$d) {
                    $d['type_valeur'] = $typeForce;
                }
                unset($d);
            }

            $typeValeur = $donnees['type_valeur'] ?? '10';
            $global     = $donnees['global']      ?? null;
            $codeEnreg  = $global['code_enregistrement'] ?? '21';
            $codeDevise = $global['code_devise']          ?? 'TND';

            $this->log('info', "Parsing terminé : {$donnees['total_lignes']} lignes, type {$typeValeur}", [
                'fichier_id' => $fichier->id,
                'etape'      => 'PARSING',
            ]);

            // Enregistrer erreurs de parsing
            foreach ($donnees['erreurs_parsing'] ?? [] as $ep) {
                TcRejet::create([
                    'fichier_id'      => $fichier->id,
                    'detail_id'       => null,
                    'code_rejet'      => 'PARSE_ERR',
                    'motif_rejet'     => $ep['raison'] ?? 'Erreur parsing',
                    'etape_detection' => 'PARSING',
                ]);
            }

            // 3. Persister l'enregistrement global
            if ($global) {
                TcEnrGlobal::create([
                    'fichier_id'              => $fichier->id,
                    'sens'                    => $global['sens']             ?? '1',
                    'code_valeur'             => $global['code_valeur']      ?? $typeValeur,
                    'nature_remettant'        => $global['nature_remettant'] ?? '1',
                    'code_remettant'          => $global['code_remettant']   ?? '26',
                    'code_centre_regional'    => $global['code_centre']      ?? '999',
                    'date_operation'          => $global['date_operation']   ?? '',
                    'numero_lot'              => $global['numero_lot']       ?? '0001',
                    'code_devise'             => $codeDevise ?: 'TND',
                    'montant_total_virements' => (float)($global['montant_total'] ?? 0),
                    'nombre_total_virements'  => (int)($global['nombre_total']    ?? 0),
                ]);

                $fichier->update([
                    'type_valeur'         => $typeForce ?: $typeValeur,
                    'code_enregistrement' => $codeEnreg,
                    'sens'                => $global['sens'] ?? '1',
                    'code_devise'         => $codeDevise ?: 'TND',
                    'date_operation'      => $global['date_operation'] ?? '',
                ]);
            }

            // 4. Persister les détails et valider
            $nbValides     = 0;
            $nbRejetes     = 0;
            $montantTotal  = 0.0;
            $detailsModels = [];

            // ── Stats ML (avec agrégation des raisons) ────────────
            $mlStats = [
                'total'        => 0,
                'rouge'        => 0,
                'orange'       => 0,
                'vert'         => 0,
                'score_global' => 0,
                'scores'       => [],
                'disponible'   => false,
                'top_raisons'  => [],
            ];

            foreach ($donnees['details'] as $detail) {
                $detailModel = TcEnrDetail::create(
                    $this->persistence->construireDataDetail($fichier->id, $detail)
                );
                $detailsModels[] = $detailModel;

                // ── Estimation ML ──────────────────────────────────
                $mlResult = $this->mlScoring->estimer($detail, $global ?? []);

                if ($mlResult['score'] !== null) {
                    $mlStats['disponible'] = true;
                    $mlStats['total']++;
                    $mlStats['scores'][] = $mlResult['score'];

                    match($mlResult['couleur']) {
                        'rouge'  => $mlStats['rouge']++,
                        'orange' => $mlStats['orange']++,
                        default  => $mlStats['vert']++,
                    };

                    // ── Agrégation des raisons ─────────────────────
                    foreach ($mlResult['explications'] ?? [] as $expl) {
                        $key = $expl['libelle'] ?? 'Inconnu';
                        if (!isset($mlStats['top_raisons'][$key])) {
                            $mlStats['top_raisons'][$key] = [
                                'libelle'    => $expl['libelle'] ?? 'Inconnu',
                                'detail'     => $expl['detail']  ?? '',
                                'gravite'    => $expl['gravite'] ?? 'faible',
                                'occurences' => 0,
                            ];
                        }
                        $mlStats['top_raisons'][$key]['occurences']++;
                    }
                }

                // ── Validation SIBTEL ──────────────────────────────
                $motifRejet = trim($detail['motif_rejet'] ?? '00000000');
                $estRejete  = !empty($motifRejet) && $motifRejet !== '00000000';

                if ($estRejete) {
                    $detailModel->update(['statut' => 'REJETE']);
                    $nbRejetes++;
                    TcRejet::create([
                        'fichier_id'      => $fichier->id,
                        'detail_id'       => $detailModel->id,
                        'code_rejet'      => substr($motifRejet, 0, 2),
                        'motif_rejet'     => 'Rejet SIBTEL code : ' . substr($motifRejet, 0, 2),
                        'etape_detection' => 'SIBTEL',
                    ]);
                } else {
                    // Validation individuelle BTL
                    $valide = $this->validator->valider([
                        'global'  => $global,
                        'details' => [$detail],
                    ]);

                    if ($valide) {
                        $detailModel->update(['statut' => 'VALIDE']);
                        $nbValides++;
                        $montantTotal += (float)($detail['montant'] ?? 0);
                    } else {
                        $detailModel->update(['statut' => 'REJETE']);
                        $nbRejetes++;
                        foreach ($this->validator->getErreurs() as $erreur) {
                            TcRejet::create([
                                'fichier_id'      => $fichier->id,
                                'detail_id'       => $detailModel->id,
                                'code_rejet'      => 'VALID_ERR',
                                'motif_rejet'     => $erreur,
                                'etape_detection' => 'VALIDATION',
                            ]);
                        }
                    }
                }
            }

            // ── Calcul score global ML ──────────────────────────────
            if ($mlStats['disponible'] && count($mlStats['scores']) > 0) {
                $mlStats['score_global'] = (int)(array_sum($mlStats['scores']) / count($mlStats['scores']));
            }
            unset($mlStats['scores']);

            // ── Tri & top 5 des raisons ───────────────────
            if (!empty($mlStats['top_raisons'])) {
                $raisons = array_values($mlStats['top_raisons']);
                usort($raisons, fn($a, $b) => $b['occurences'] - $a['occurences']);
                $mlStats['top_raisons'] = array_slice($raisons, 0, 5);
            } else {
                $mlStats['top_raisons'] = [];
            }

            $this->log('info', "Validation : {$nbValides} valides, {$nbRejetes} rejetés", [
                'fichier_id' => $fichier->id,
                'etape'      => 'VALIDATION',
            ]);

            // 5. Générer le XML pour les transactions valides
            if ($nbValides > 0) {
                $donneesPourXml = $donnees;
                $donneesPourXml['details'] = array_values(array_filter(
                    $donnees['details'],
                    fn($d, $i) => isset($detailsModels[$i]) && $detailsModels[$i]->statut === 'VALIDE',
                    ARRAY_FILTER_USE_BOTH
                ));

                $xml       = $this->transformer->transformer($donneesPourXml);
                $valideXsd = $this->validerXml($xml);

                TcXmlProduit::create([
                    'fichier_id'   => $fichier->id,
                    'type_message' => $this->transformer->getTypeMessage(),
                    'contenu_xml'  => $xml,
                    'valide_xsd'   => $valideXsd,
                ]);

                $this->log('info', "XML généré : " . $this->transformer->getTypeMessage(), [
                    'fichier_id' => $fichier->id,
                    'etape'      => 'TRANSFORMATION',
                ]);
            }

            // 6. Statut final
            $user = Auth::user();
            $role = $user->role ?? 'admin';

            $statut = match(true) {
                $nbRejetes === 0 && $nbValides > 0 && $role === 'operateur' => 'EN_ATTENTE_VALIDATION',
                $nbRejetes === 0 && $nbValides > 0                          => 'TRAITE',
                $nbValides > 0 && $nbRejetes > 0 && $role === 'operateur'  => 'EN_ATTENTE_VALIDATION',
                $nbValides > 0 && $nbRejetes > 0                            => 'TRAITE_PARTIEL',
                default                                                      => 'ERREUR',
            };

            $fichier->update([
                'statut'          => $statut,
                'nb_transactions' => count($donnees['details']),
                'nb_rejets'       => $nbRejetes,
                'montant_total'   => $montantTotal,
                'uploaded_by'     => $user->id,
            ]);
            // Notifier superviseurs si operateur a soumis un fichier en attente
            $this->notifier->notifierNouveauFichier($fichier, $user, $nbValides, $statut);

            DB::commit();

            $resultat = [
                'succes'      => true,
                'fichier_id'  => $fichier->id,
                'type_valeur' => $typeValeur,
                'message'     => "Fichier traité avec succès",
                'stats'       => [
                    'total'   => count($donnees['details']),
                    'valides' => $nbValides,
                    'rejetes' => $nbRejetes,
                    'statut'  => $statut,
                ],
                'ml' => $mlStats,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $resultat['message'] = "Erreur : " . $e->getMessage();
            if (isset($fichier)) {
                $fichier->update(['statut' => 'ERREUR']);
                $this->log('error', $e->getMessage(), [
                    'fichier_id' => $fichier->id,
                    'etape'      => 'SYSTEME',
                ]);
                $resultat['fichier_id'] = $fichier->id;
            }
        }

        return $resultat;
    }

    // ──────────────────────────────────────────────────────────────
    // CONSTRUCTION DES DONNÉES DETAIL SELON LE TYPE

    protected function validerXml(string $xml): bool
    {
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        Log::log($level, $message, $context);

        if (isset($context['fichier_id'])) {
            $etape      = $context['etape'] ?? 'GENERAL';
            $contexteDb = array_diff_key($context, array_flip(['fichier_id', 'etape']));
            match($level) {
                'error'   => $this->logService->erreur($context['fichier_id'], $etape, $message, $contexteDb),
                'warning' => $this->logService->warning($context['fichier_id'], $etape, $message, $contexteDb),
                default   => $this->logService->info($context['fichier_id'], $etape, $message, $contexteDb),
            };
        }
    }
}