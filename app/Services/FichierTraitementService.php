<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

/**
 * FichierTraitementService — Pipeline complet SIBTEL
 *
 * Corrections :
 *  - type_valeur string (pas 'TND')
 *  - Persistance de TOUS les champs par type de valeur
 *  - Montant déjà en TND, pas de double conversion
 *  - Validation globale + individuelle correcte
 *  - Mise à jour nb_transactions, nb_rejets, montant_total
 */
class FichierTraitementService
{
    protected ParserInterface      $parser;
    protected ValidatorInterface   $validator;
    protected TransformerInterface $transformer;
    protected LogService           $logService;

    public function __construct(
        ParserInterface      $parser,
        ValidatorInterface   $validator,
        TransformerInterface $transformer,
        LogService           $logService
    ) {
        $this->parser      = $parser;
        $this->validator   = $validator;
        $this->transformer = $transformer;
        $this->logService  = $logService;
    }

    public function traiter(string $cheminFichier, string $typeForce = ''): array
    {
        $resultat = [
            'succes'     => false,
            'fichier_id' => null,
            'message'    => '',
            'stats'      => [],
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

            foreach ($donnees['details'] as $detail) {
                $detailModel = TcEnrDetail::create(
                    $this->construireDataDetail($fichier->id, $detail)
                );
                $detailsModels[] = $detailModel;


                // Verification motif rejet SIBTEL avant validation
                $motifRejet = trim($detail['motif_rejet'] ?? '00000000');
                $estRejete  = !empty($motifRejet) && $motifRejet !== '00000000';

                if ($estRejete) {
                    // Transaction rejetee par SIBTEL - pas besoin de valider
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
            $user = \Illuminate\Support\Facades\Auth::user();
            $role = $user->role ?? 'admin';

            $statut = match(true) {
                $nbRejetes === 0 && $nbValides > 0 && $role === 'operateur' => 'EN_ATTENTE_VALIDATION',
                $nbRejetes === 0 && $nbValides > 0 => 'TRAITE',
                $nbValides > 0 && $nbRejetes > 0 && $role === 'operateur' => 'EN_ATTENTE_VALIDATION',
                $nbValides > 0 && $nbRejetes > 0   => 'TRAITE_PARTIEL',
                default                             => 'ERREUR',
            };

            $fichier->update([
                'statut'          => $statut,
                'nb_transactions' => count($donnees['details']),
                'nb_rejets'       => $nbRejetes,
                'montant_total'   => $montantTotal,
                'uploaded_by'     => $user->id,
            ]);

            // Notifier superviseurs si operateur
            if ($role === 'operateur' && $statut === 'EN_ATTENTE_VALIDATION') {
                $superviseurs = \App\Models\User::whereIn('role', ['superviseur', 'admin'])->get();
                foreach ($superviseurs as $sup) {
                    \App\Models\TcNotification::create([
                        'user_id'    => $sup->id,
                        'titre'      => 'Nouveau fichier a valider',
                        'message'    => "L'operateur {$user->name} a soumis {$fichier->nom_fichier} — {$nbValides} transactions valides.",
                        'type'       => 'UPLOAD',
                        'fichier_id' => $fichier->id,
                    ]);
                }
            }

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
    // ──────────────────────────────────────────────────────────────
    private function construireDataDetail(int $fichierId, array $detail): array
    {
        $typeValeur = $detail['type_valeur'] ?? '10';

        $data = [
            'fichier_id'            => $fichierId,
            'type_valeur'           => $typeValeur,
            'numero_virement'       => (int)($detail['numero_virement'] ?? 0),
            'code_enregistrement'   => $detail['code_enregistrement'] ?? '21',
            'montant'               => (float)($detail['montant'] ?? 0),
            'rib_donneur'           => $detail['rib_donneur']     ?? '',
            'nom_donneur'           => mb_substr($detail['nom_donneur']     ?? '', 0, 140),
            'rib_beneficiaire'      => $detail['rib_beneficiaire'] ?? '',
            'nom_beneficiaire'      => mb_substr($detail['nom_beneficiaire'] ?? '', 0, 140),
            'code_institution_dest' => $detail['code_institution_dest'] ?? '',
            'code_centre_dest'      => $detail['code_centre_dest']      ?? '',
            'motif_rejet'           => $detail['motif_rejet'] ?? '',
            'zone_libre'            => $detail['zone_libre']  ?? '',
            'statut'                => 'EN_ATTENTE',
        ];

        switch ($typeValeur) {
            case '10':
                $data['reference_dossier'] = $detail['reference_dossier'] ?? '';
                $data['code_enreg_comp']   = $detail['code_enreg_comp']   ?? '';
                $data['nb_enreg_comp']     = (int)($detail['nb_enreg_comp'] ?? 0);
                $data['motif_operation']   = mb_substr($detail['motif_operation'] ?? '', 0, 45);
                $data['date_compensation'] = $detail['date_compensation'] ?? '';
                $data['situation_donneur'] = $detail['situation_donneur'] ?? '';
                $data['type_compte']       = $detail['type_compte']       ?? '';
                $data['nature_compte']     = $detail['nature_compte']     ?? '';
                $data['existence_dossier'] = $detail['existence_dossier'] ?? '';
                $data['code_suivi']        = $detail['code_suivi']        ?? '';
                break;

            case '20':
                $data['rib_payeur']          = $detail['rib_payeur']          ?? '';
                $data['rib_creancier']       = $detail['rib_creancier']       ?? '';
                $data['code_emetteur']       = $detail['code_emetteur']       ?? '';
                $data['ref_contrat']         = $detail['ref_contrat']         ?? '';
                $data['libelle_prelevement'] = mb_substr($detail['libelle_prelevement'] ?? '', 0, 50);
                $data['date_compensation']   = $detail['date_compensation']   ?? '';
                $data['date_echeance']       = $detail['date_echeance']       ?? '';
                $data['code_payeur']         = $detail['code_payeur']         ?? '';
                $data['motif_operation']     = mb_substr($detail['motif_operation'] ?? '', 0, 45);
                break;

            case '30':
            case '33':
                $data['rib_tireur']             = $detail['rib_tireur']             ?? '';
                $data['numero_cheque']          = $detail['numero_cheque']          ?? '';
                $data['date_emission']          = $detail['date_emission']          ?? '';
                $data['lieu_emission']          = $detail['lieu_emission']          ?? '';
                $data['situation_beneficiaire'] = $detail['situation_beneficiaire'] ?? '';
                $data['nature_compte']          = $detail['nature_compte']          ?? '';
                break;

            case '31':
                $data['rib_tireur']      = $detail['rib_tireur']      ?? '';
                $data['numero_cheque']   = $detail['numero_cheque']   ?? '';
                $data['date_emission']   = $detail['date_emission']   ?? '';
                $data['lieu_emission']   = $detail['lieu_emission']   ?? '';
                $data['date_cnp']        = $detail['date_cnp']        ?? '';
                $data['numero_cnp']      = $detail['numero_cnp']      ?? '';
                $data['montant_reclame'] = (float)($detail['montant_reclame'] ?? 0);
                break;

            case '32':
                $data['rib_tireur']       = $detail['rib_tireur']       ?? '';
                $data['numero_cheque']    = $detail['numero_cheque']    ?? '';
                $data['date_emission']    = $detail['date_emission']    ?? '';
                $data['lieu_emission']    = $detail['lieu_emission']    ?? '';
                $data['date_cnp']         = $detail['date_cnp']         ?? '';
                $data['numero_cnp']       = $detail['numero_cnp']       ?? '';
                $data['montant_reclame']  = (float)($detail['montant_reclame']  ?? 0);
                $data['montant_interets'] = (float)($detail['montant_interets'] ?? 0);
                break;

            case '40': case '41': case '42': case '43':
                $data['numero_lettre_change']    = $detail['numero_lettre_change']    ?? '';
                $data['rib_tire']                = $detail['rib_tire']                ?? '';
                $data['rib_tire_initial']        = $detail['rib_tire_initial']        ?? '';
                $data['rib_cedant']              = $detail['rib_cedant']              ?? '';
                $data['nom_cedant']              = mb_substr($detail['nom_cedant'] ?? '', 0, 30);
                $data['nom_tire']                = mb_substr($detail['nom_tire']   ?? '', 0, 30);
                $data['ref_commerciales_tire']   = $detail['ref_commerciales_tire']   ?? '';
                $data['ref_commerciales_tireur'] = $detail['ref_commerciales_tireur'] ?? '';
                $data['montant_interets']        = (float)($detail['montant_interets']      ?? 0);
                $data['montant_frais_protest']   = (float)($detail['montant_frais_protest'] ?? 0);
                $data['code_acceptation']        = $detail['code_acceptation']        ?? '';
                $data['code_endossement']        = $detail['code_endossement']        ?? '';
                $data['date_echeance_ldc']       = $detail['date_echeance']           ?? '';
                $data['date_echeance_initiale']  = $detail['date_echeance_initiale']  ?? '';
                $data['date_creation']           = $detail['date_creation']           ?? '';
                $data['lieu_creation']           = mb_substr($detail['lieu_creation'] ?? '', 0, 30);
                $data['code_ordre_payer']        = $detail['code_ordre_payer']        ?? '';
                $data['situation_cedant']        = $detail['situation_cedant']        ?? '';
                $data['nature_compte']           = $detail['nature_compte']           ?? '';
                $data['code_risque_bct']         = $detail['code_risque_bct']         ?? '';
                $data['date_compensation']       = $detail['date_compensation']       ?? '';
                break;

            case '82':
                $data['rib_tireur']             = $detail['rib_tireur']             ?? '';
                $data['numero_cheque']          = $detail['numero_cheque']          ?? '';
                $data['date_emission']          = $detail['date_emission']          ?? '';
                $data['lieu_emission']          = $detail['lieu_emission']          ?? '';
                $data['date_cnp']               = $detail['date_etablissement_cnp'] ?? '';
                $data['numero_cnp']             = $detail['numero_cnp']             ?? '';
                $data['date_presentation']      = $detail['date_presentation']      ?? '';
                $data['date_preaviss']          = $detail['date_preaviss']          ?? '';
                $data['montant_provision']      = (float)($detail['montant_provision'] ?? 0);
                $data['date_delivrance']        = $detail['date_delivrance']        ?? '';
                $data['nb_enreg_comp']          = (int)($detail['nb_enreg_comp']   ?? 0);
                $data['signature_electronique'] = $detail['signature_electronique'] ?? '';
                $data['ref_cle_publique']       = $detail['ref_cle_publique']       ?? '';
                break;

            case '83':
                $data['rib_tireur']         = $detail['rib_tireur']          ?? '';
                $data['numero_cheque']      = $detail['numero_cheque']       ?? '';
                $data['date_emission']      = $detail['date_emission']       ?? '';
                $data['lieu_emission']      = $detail['lieu_emission']       ?? '';
                $data['date_cnp']           = $detail['date_cnp']            ?? '';
                $data['numero_cnp']         = $detail['numero_cnp']          ?? '';
                $data['montant_regularise'] = (float)($detail['montant_regularise'] ?? 0);
                $data['montant_interets']   = (float)($detail['montant_interets']   ?? 0);
                break;

            case '84':
                $data['rib_tireur']         = $detail['rib_tireur']         ?? '';
                $data['numero_cheque']      = $detail['numero_cheque']      ?? '';
                $data['date_etablissement'] = $detail['date_etablissement'] ?? '';
                $data['numero_papillon']    = $detail['numero_papillon']    ?? '';
                $data['nb_enreg_comp']      = (int)($detail['nb_enreg_comp'] ?? 0);
                break;
        }

        return $data;
    }

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
