<?php

namespace App\Services;

use App\Contracts\ParserInterface;

class EnvParserService implements ParserInterface
{
    const LONGUEURS = [
        '10' => 280,
        '20' => 200,
        '30' => 160,
        '31' => 350,
        '32' => 160,
        '33' => 160,
        '40' => 380,
        '41' => 380,
        '42' => 380,
        '43' => 380,
        '82' => 350,
        '83' => 160,
        '84' => 280,
    ];

    const CHAMPS_ENTETE = [
        'sens'                => [0,  1],
        'code_valeur'         => [1,  2],
        'nature_remettant'    => [3,  1],
        'code_remettant'      => [4,  2],
        'code_centre'         => [6,  3],
        'date_operation'      => [9,  8],
        'numero_lot'          => [17, 4],
        'code_enregistrement' => [21, 2],
        'code_devise'         => [23, 3],
        'rang'                => [26, 2],
    ];

    const CHAMPS_GLOBAL_COMMUN = [
        'montant_total' => [28, 15],
        'nombre_total'  => [43, 10],
    ];

    const CHAMPS_DETAIL_VIREMENT = [
        'montant'               => [28, 15],
        'numero_virement'       => [43, 7],
        'rib_donneur'           => [50, 20],
        'nom_donneur'           => [70, 30],
        'code_institution_dest' => [100, 2],
        'code_centre_dest'      => [102, 3],
        'rib_beneficiaire'      => [105, 20],
        'nom_beneficiaire'      => [125, 30],
        'reference_dossier'     => [155, 20],
        'motif_operation'       => [175, 45],
        'situation_donneur'     => [220, 1],
        'type_compte'           => [221, 1],
        'nature_compte'         => [222, 1],
        'existence_dossier'     => [223, 1],
        'zone_libre'            => [224, 37],
    ];

    const CHAMPS_DETAIL_PRELEVEMENT = [
        'montant'               => [28, 15],
        'numero_prelevement'    => [43, 7],
        'rib_payeur'            => [50, 20],
        'code_institution_dest' => [70, 2],
        'code_centre_dest'      => [72, 3],
        'rib_creancier'         => [75, 20],
        'code_emetteur'         => [95, 6],
        'ref_contrat'           => [101, 20],
        'libelle_prelevement'   => [121, 50],
        'date_compensation'     => [171, 8],
        'motif_rejet'           => [179, 8],
        'date_echeance'         => [187, 8],
        'zone_libre'            => [195, 7],
    ];

    const CHAMPS_DETAIL_CHEQUE_30 = [
        'montant'                => [28, 15],
        'numero_cheque'          => [43, 7],
        'rib_tireur'             => [50, 20],
        'code_institution_dest'  => [70, 2],
        'code_centre_dest'       => [72, 3],
        'rib_beneficiaire'       => [75, 20],
        'nom_beneficiaire'       => [95, 30],
        'date_emission'          => [125, 8],
        'date_preaviss'          => [133, 8],
        'code_devise_position'   => [141, 3],
        'montant_provision'      => [144, 15],
        'motif_rejet'            => [159, 8],
        'zone_libre'             => [167, 25],
    ];

    const CHAMPS_DETAIL_CHEQUE_31 = [
        'montant'                 => [28, 15],
        'numero_cheque'           => [43, 7],
        'rib_tireur'              => [50, 20],
        'code_institution_dest'   => [70, 2],
        'code_centre_dest'        => [72, 3],
        'rib_beneficiaire'        => [75, 20],
        'date_emission'           => [95, 8],
        'lieu_emission'           => [103, 1],
        'date_etablissement_cnp'  => [104, 8],
        'numero_cnp'              => [112, 4],
        'date_presentation'       => [116, 8],
        'date_preaviss'           => [124, 8],
        'montant_provision'       => [132, 15],
        'date_delivrance'         => [147, 8],
        'motif_rejet'             => [155, 8],
        'nb_enreg_comp'           => [163, 2],
        'signature_electronique'  => [165, 128],
        'ref_cle_publique'        => [293, 14],
        'zone_libre'              => [307, 43],
    ];

    const CHAMPS_DETAIL_CHEQUE_32 = [
        'montant'                => [28, 15],
        'numero_cheque'          => [43, 7],
        'rib_tireur'             => [50, 20],
        'code_institution_dest'  => [70, 2],
        'code_centre_dest'       => [72, 3],
        'rib_beneficiaire'       => [75, 20],
        'date_emission'          => [95, 8],
        'lieu_emission'          => [103, 1],
        'date_cnp'               => [104, 8],
        'numero_cnp'             => [112, 4],
        'code_devise_position'   => [116, 3],
        'montant_regularise'     => [119, 15],
        'montant_interets'       => [134, 15],
        'zone_libre'             => [149, 13],
    ];

    const CHAMPS_DETAIL_CHEQUE_33 = [
        'montant'               => [28, 15],
        'numero_cheque'         => [43, 7],
        'rib_tireur'            => [50, 20],
        'code_institution_dest' => [70, 2],
        'code_centre_dest'      => [72, 3],
        'rib_beneficiaire'      => [75, 20],
        'date_emission'         => [95, 8],
        'lieu_emission'         => [103, 1],
        'motif_rejet'           => [104, 8],
        'zone_libre'            => [112, 50],
    ];

    const CHAMPS_DETAIL_LETTRE_CHANGE = [
        'montant'                => [28, 15],
        'montant_interets'       => [43, 15],
        'montant_frais_protest'  => [58, 15],
        'numero_lettre_change'   => [73, 12],
        'rib_tire'               => [85, 20],
        'rib_tire_initial'       => [105, 20],
        'code_institution_dest'  => [125, 2],
        'code_centre_dest'       => [127, 3],
        'rib_cedant'             => [130, 20],
        'nom_cedant'             => [150, 30],
        'nom_tire'               => [180, 30],
        'ref_commerciales_tire'  => [210, 30],
        'code_acceptation'       => [240, 1],
        'code_endossement'       => [241, 1],
        'date_echeance'          => [242, 8],
        'date_echeance_initiale' => [250, 8],
        'date_creation'          => [258, 8],
        'lieu_creation'          => [266, 30],
        'ref_commerciales_tireur'=> [296, 30],
        'code_ordre_payer'       => [326, 1],
        'situation_cedant'       => [327, 1],
        'nature_compte'          => [328, 1],
        'date_compensation'      => [329, 8],
        'motif_rejet'            => [337, 8],
        'code_risque_bct'        => [345, 6],
        'zone_libre'             => [351, 29],
    ];

    const CHAMPS_DETAIL_PAPILLON = [
        'montant'                 => [28, 15],
        'numero_cheque'           => [43, 7],
        'rib_tireur'              => [50, 20],
        'code_institution_dest'   => [70, 2],
        'code_centre_dest'        => [72, 3],
        'rib_beneficiaire'        => [75, 20],
        'date_emission'           => [95, 8],
        'lieu_emission'           => [103, 1],
        'date_etablissement'      => [104, 8],
        'numero_papillon'         => [112, 4],
        'motif_rejet'             => [116, 8],
        'nb_enreg_comp'           => [124, 2],
        'zone_libre'              => [126, 154],
    ];

    public function parse(string $cheminFichier): array
    {
        if (!file_exists($cheminFichier)) {
            throw new \RuntimeException("Fichier introuvable : {$cheminFichier}");
        }

        $contenu = file_get_contents($cheminFichier);
        $contenu = str_replace(["\r\n", "\r"], "\n", $contenu);
        $lignes  = explode("\n", $contenu);

        $resultat = [
            'nom_fichier'     => basename($cheminFichier),
            'chemin_complet'  => $cheminFichier,
            'type_valeur'     => null,
            'longueur'        => 280,
            'global'          => null,
            'details'         => [],
            'complementaires' => [],
            'erreurs_parsing' => [],
            'total_lignes'    => 0,
        ];

        $typeValeur = $this->detecterTypeDepuisNom(basename($cheminFichier));
        $longueur   = self::LONGUEURS[$typeValeur] ?? 280;
        $resultat['type_valeur'] = $typeValeur;
        $resultat['longueur']    = $longueur;

        foreach ($lignes as $numero => $ligne) {
            $ligne = rtrim($ligne, "\r");
            if (empty(trim($ligne))) continue;

            $resultat['total_lignes']++;
            $ligne  = str_pad($ligne, $longueur);
            $entete = $this->extraireChamps($ligne, self::CHAMPS_ENTETE);

            if (!empty($entete['code_valeur'])) {
                $typeDetecte = trim($entete['code_valeur']);
                if (array_key_exists($typeDetecte, self::LONGUEURS)) {
                    $typeValeur = $typeDetecte;
                    $longueur   = self::LONGUEURS[$typeValeur];
                    $resultat['type_valeur'] = $typeValeur;
                    $resultat['longueur']    = $longueur;
                    $ligne  = str_pad($ligne, $longueur);
                    $entete = $this->extraireChamps($ligne, self::CHAMPS_ENTETE);
                }
            }

            $rang = trim($entete['rang']);

            if ($rang === '00') {
                $global = array_merge(
                    $entete,
                    $this->extraireChamps($ligne, self::CHAMPS_GLOBAL_COMMUN)
                );
                $global['ligne_numero'] = $numero + 1;
                $resultat['global']     = $global;

            } elseif (is_numeric($rang) && (int)$rang >= 1) {
                $champsDetail = $this->getChampsDetail($typeValeur);
                $detail       = array_merge(
                    $entete,
                    $this->extraireChamps($ligne, $champsDetail)
                );
                $detail['ligne_numero'] = $numero + 1;
                $detail['type_valeur']  = $typeValeur;
                $detail = $this->normaliserDetail($detail, $typeValeur);
                $resultat['details'][] = $detail;

            } else {
                $resultat['erreurs_parsing'][] = [
                    'ligne'   => $numero + 1,
                    'contenu' => substr($ligne, 0, 50),
                    'raison'  => "Rang non reconnu : '{$rang}'",
                ];
            }
        }

        return $resultat;
    }

    public function supporte(string $cheminFichier): bool
    {
        $extension = strtoupper(pathinfo($cheminFichier, PATHINFO_EXTENSION));
        return in_array($extension, ['ENV', 'PAK']);
    }

    private function getChampsDetail(string $typeValeur): array
    {
        return match($typeValeur) {
            '20'                    => self::CHAMPS_DETAIL_PRELEVEMENT,
            '30'                    => self::CHAMPS_DETAIL_CHEQUE_30,
            '31', '82'              => self::CHAMPS_DETAIL_CHEQUE_31,
            '32', '33', '83'        => self::CHAMPS_DETAIL_CHEQUE_32,
            '40', '41', '42', '43'  => self::CHAMPS_DETAIL_LETTRE_CHANGE,
            '84'                    => self::CHAMPS_DETAIL_PAPILLON,
            default                 => self::CHAMPS_DETAIL_VIREMENT,
        };
    }

    private function normaliserDetail(array $detail, string $typeValeur): array
    {
        $detail['montant'] = $this->formatMontant($detail['montant'] ?? '0');

        switch ($typeValeur) {
            case '20':
                $detail['rib_donneur']      = $detail['rib_payeur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['rib_beneficiaire'] = $detail['rib_creancier'] ?? '';
                $detail['nom_beneficiaire'] = '';
                $detail['motif_operation']  = $detail['libelle_prelevement'] ?? '';
                $detail['numero_virement']  = $detail['numero_prelevement'] ?? '0';
                break;
            case '30':
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'CHEQUE PRESENTATION';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                if (!empty($detail['montant_provision'])) {
                    $detail['montant_provision'] = $this->formatMontant($detail['montant_provision']);
                }
                break;
            case '31': case '82':
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'CNP - PAIEMENT PARTIEL';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                if (!empty($detail['montant_provision'])) {
                    $detail['montant_provision'] = $this->formatMontant($detail['montant_provision']);
                }
     
                break;
            case '32': case '83':
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'ARP - APRES REGULARISATION';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                break;
            case '33':
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'CHEQUE RETOUR';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                break;
            case '40': case '41': case '42': case '43':
                $detail['rib_donneur']      = $detail['rib_tire'] ?? '';
                $detail['nom_donneur']      = $detail['nom_tire'] ?? '';
                $detail['rib_beneficiaire'] = $detail['rib_cedant'] ?? '';
                $detail['nom_beneficiaire'] = $detail['nom_cedant'] ?? '';
                $detail['motif_operation']  = 'LETTRE DE CHANGE';
                $detail['numero_virement']  = $detail['numero_lettre_change'] ?? '0';
                break;
            case '84':
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'PAPILLON';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                break;
        }

        return $detail;
    }

    private function detecterTypeDepuisNom(string $nomFichier): string
    {
        $parties = explode('-', strtoupper($nomFichier));
        if (isset($parties[2]) && is_numeric($parties[2])) {
            return $parties[2];
        }
        return '10';
    }

    private function extraireChamps(string $ligne, array $champs): array
    {
        $donnees = [];
        foreach ($champs as $nom => [$debut, $longueur]) {
            $donnees[$nom] = trim(substr($ligne, $debut, $longueur));
        }
        return $donnees;
    }

    private function formatMontant(string $montant): float
    {
        $montant = trim(ltrim($montant, '0'));
        if (empty($montant)) return 0.0;
        return (float)$montant / 1000;
    }
}