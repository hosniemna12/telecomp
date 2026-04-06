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

class FichierTraitementService
{
    protected ParserInterface $parser;
    protected ValidatorInterface $validator;
    protected TransformerInterface $transformer;

    public function __construct(
        ParserInterface $parser,
        ValidatorInterface $validator,
        TransformerInterface $transformer
    ) {
        $this->parser      = $parser;
        $this->validator   = $validator;
        $this->transformer = $transformer;
    }

    protected function formatMontant($montant)
    {
        return (float)str_replace(',', '.', $montant);
    }

    protected function validateXml($xml)
    {
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function log(string $level, string $message, array $context = [])
    {
        Log::$level($message, $context);
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
            $typeValeur = $typeForce ?: 'TND';

            $fichier = TcFichier::create([
                'nom_fichier'         => basename($cheminFichier),
                'chemin_complet'      => $cheminFichier,
                'type_valeur'         => $typeValeur,
                'code_enregistrement' => 21,
                'statut'              => 'EN_COURS',
                'date_reception'      => now(),
            ]);

            $this->log('info', "Fichier recu : {$fichier->nom_fichier} — Type : {$typeValeur}", [
                'fichier_id' => $fichier->id,
                'etape'      => 'ACQUISITION'
            ]);

            $donnees = $this->parser->parse($cheminFichier);

            if ($typeForce) {
                foreach ($donnees['details'] as $detail) {
                    $detail['type_valeur'] = $typeForce;
                }
            }

            $this->log('info', "Parsing termine : {$donnees['total_lignes']} lignes", [
                'fichier_id' => $fichier->id,
                'etape'      => 'PARSING',
                'details'    => count($donnees['details'])
            ]);

            if ($donnees['global']) {
                $global = $donnees['global'];
                TcEnrGlobal::create([
                    'fichier_id'             => $fichier->id,
                    'sens'                   => $global['sens'],
                    'code_valeur'            => $global['code_valeur'],
                    'nature_remettant'       => $global['nature_remettant'],
                    'code_remettant'         => $global['code_remettant'],
                    'code_centre_regional'   => $global['code_centre'],
                    'date_operation'         => $global['date_operation'],
                    'numero_lot'             => $global['numero_lot'],
                    'code_devise'            => $global['code_devise'] ?: 'TND',
                    'montant_total_virements'=> $this->formatMontant($global['montant_total']),
                    'nombre_total_virements' => (int)$global['nombre_total'],
                ]);

                $fichier->update([
                    'sens'        => $global['sens'],
                    'code_devise' => $global['code_devise'] ?: 'TND',
                    'type_valeur' => $typeForce ?: $global['code_valeur'],
                ]);
            }

            $nbValides = 0;
            $nbRejetes = 0;

            foreach ($donnees['details'] as $detail) {
                $detailModel = TcEnrDetail::create([
                    'fichier_id'            => $fichier->id,
                    'numero_virement'       => (int)($detail['numero_virement'] ?? 0),
                    'montant'               => $this->formatMontant($detail['montant']),
                    'rib_donneur'           => $detail['rib_donneur'] ?? '',
                    'nom_donneur'           => $detail['nom_donneur'] ?? '',
                    'rib_beneficiaire'      => $detail['rib_beneficiaire'] ?? '',
                    'nom_beneficiaire'      => $detail['nom_beneficiaire'] ?? '',
                    'code_institution_dest' => (int)($detail['code_institution_dest'] ?? 0),
                    'motif_operation'       => $detail['motif_operation'] ?? '',
                    'reference_dossier'     => $detail['reference_dossier'] ?? '',
                    'situation_donneur'     => $detail['situation_donneur'] ?? '',
                    'type_compte_donneur'   => $detail['type_compte'] ?? '',
                    'statut'                => 'EN_ATTENTE',
                ]);

                $valide = $this->validator->valider([
                    'global'  => $donnees['global'],
                    'details' => [$detail],
                ]);

                if ($valide) {
                    $detailModel->update(['statut' => 'VALIDE']);
                    $nbValides++;
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

            $this->log('info', "Validation : {$nbValides} valides, {$nbRejetes} rejetes", [
                'fichier_id' => $fichier->id,
                'etape'      => 'VALIDATION',
                'valides'    => $nbValides,
                'rejetes'    => $nbRejetes
            ]);

            if ($nbValides > 0) {
                $xml = $this->transformer->transformer($donnees);

                TcXmlProduit::create([
                    'fichier_id'   => $fichier->id,
                    'type_message' => $this->transformer->getTypeMessage(),
                    'contenu_xml'  => $xml,
                    'valide_xsd'   => $this->validateXml($xml),
                ]);

                $this->log('info', "XML genere : " . $this->transformer->getTypeMessage(), [
                    'fichier_id' => $fichier->id,
                    'etape'      => 'TRANSFORMATION'
                ]);
            }

            $statut = $nbRejetes === 0 ? 'TRAITE'
                    : ($nbValides > 0  ? 'TRAITE_PARTIEL' : 'ERREUR');

            $fichier->update(['statut' => $statut]);

            DB::commit();

            $resultat = [
                'succes'      => true,
                'fichier_id'  => $fichier->id,
                'type_valeur' => $typeValeur,
                'message'     => "Fichier traite avec succes",
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
                    'exception'  => get_class($e)
                ]);
                $resultat['fichier_id'] = $fichier->id;
            }
        }

        return $resultat;
    }
}