<?php

namespace App\Services\Persistence;

use App\Models\TcFichier;
use App\Models\TcEnrGlobal;
use App\Models\TcEnrDetail;
use App\Models\TcRejet;

/**
 * Service de persistence BDD pour le traitement des fichiers SIBTEL.
 *
 * Centralise toutes les operations de sauvegarde :
 * - Construction des donnees detail selon les 10 types SIBTEL (10, 20, 30-33, 40-43, 82-84)
 * - Creation/mise a jour des entites TcFichier, TcEnrGlobal, TcEnrDetail, TcRejet
 *
 * Ne gere PAS les transactions DB : c'est la responsabilite de l'orchestrateur.
 */
class FichierPersistenceService
{
    public function construireDataDetail(int $fichierId, array $detail): array
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
}