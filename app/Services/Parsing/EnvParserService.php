<?php

namespace App\Services\Parsing;

use App\Contracts\ParserInterface;

/**
 * EnvParserService — Corrigé selon specs officielles SIBTEL (Guide 01/03/2010)
 * et document BFI/CarthagoCompensCentre ISO 20022.
 *
 * Corrections appliquées :
 *  - Virement (10) : motif_operation [178,45] (était 175), champs décalés +19
 *  - Chèque 30     : structure 160 car. sans montant_provision ni date_preaviss
 *  - Chèque 31     : champs corrects selon p.44
 *  - Chèque 32/ARP : structure 160 car. correcte (p.45 + p.51 ARP=83)
 *  - CNP 82        : identique au 31 (350 car.)
 *  - ARP 83        : identique au 32 (160 car.)
 *  - Papillon 84   : positions correctes (p.52)
 */
class EnvParserService implements ParserInterface
{
    // ──────────────────────────────────────────────────────────────
    // LONGUEURS PAR TYPE DE VALEUR (en caractères par ligne)
    // ──────────────────────────────────────────────────────────────
    const LONGUEURS = [
        '10' => 280,   // Virement
        '20' => 200,   // Prélèvement
        '30' => 160,   // Chèque présenté 1ère fois
        '31' => 160,   // Chèque CNP (paiement partiel) — NB: 160 car. (p.44)
        '32' => 160,   // Chèque ARP (après ARP)
        '33' => 160,   // Chèque retour (code 33 non explicité mais même structure)
        '40' => 380,   // Lettre de change
        '41' => 380,
        '42' => 380,
        '43' => 380,
        '80' => 200,   // Domiciliation
        '82' => 350,   // CNP (certificat non-paiement) — 350 car.
        '83' => 160,   // ARP (attestation reconstitution provision) — 160 car.
        '84' => 280,   // Papillon
    ];

    // ──────────────────────────────────────────────────────────────
    // ENTÊTE COMMUN (positions identiques pour tous les types)
    // Spec SIBTEL p.37 — champs 1 à 10
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_ENTETE = [
        'sens'                => [0,  1],   // Champ 1 : Sens
        'code_valeur'         => [1,  2],   // Champ 2 : Code valeur
        'nature_remettant'    => [3,  1],   // Champ 3 : Nature remettant
        'code_remettant'      => [4,  2],   // Champ 4 : Code remettant
        'code_centre'         => [6,  3],   // Champ 5 : Code centre régional
        'date_operation'      => [9,  8],   // Champ 6 : Date opération (DDMMYYYY)
        'numero_lot'          => [17, 4],   // Champ 7 : Numéro du lot
        'code_enregistrement' => [21, 2],   // Champ 8 : Code enregistrement (21=présentation, 22=rejet)
        'code_devise'         => [23, 3],   // Champ 9 : Code devise
        'rang'                => [26, 2],   // Champ 10: Rang (00=global, 01-xx=détail, 50=assignation postale)
    ];

    // ──────────────────────────────────────────────────────────────
    // ENREGISTREMENT GLOBAL (rang=00)
    // Champs 11-12 communs à tous les types
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_GLOBAL_COMMUN = [
        'montant_total' => [28, 15],  // Champ 11 : Montant total
        'nombre_total'  => [43, 10],  // Champ 12 : Nombre total
    ];

    // ──────────────────────────────────────────────────────────────
    // VIREMENT (type 10) — 280 caractères
    // Spec SIBTEL p.37-38 — Enregistrement détail
    // CORRECTIONS APPLIQUÉES vs code précédent :
    //   - code_enreg_comp   [175, 1]  AJOUTÉ
    //   - nb_enreg_comp     [176, 2]  AJOUTÉ
    //   - motif_operation   [178, 45] CORRIGÉ (était 175)
    //   - date_compensation [223, 8]  AJOUTÉ
    //   - motif_rejet       [231, 8]  AJOUTÉ
    //   - situation_donneur [239, 1]  CORRIGÉ (était 220)
    //   - type_compte       [240, 1]  CORRIGÉ (était 221)
    //   - nature_compte     [241, 1]  CORRIGÉ (était 222)
    //   - existence_dossier [242, 1]  CORRIGÉ (était 223)
    //   - zone_libre        [243, 37] CORRIGÉ (était 224)
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_DETAIL_VIREMENT = [
        'montant'               => [28,  15],  // Champ 11
        'numero_virement'       => [43,  7],   // Champ 12
        'rib_donneur'           => [50,  20],  // Champ 13 : RIB donneur d'ordres
        'nom_donneur'           => [70,  30],  // Champ 14 : Nom donneur d'ordres
        'code_institution_dest' => [100, 2],   // Champ 15
        'code_centre_dest'      => [102, 3],   // Champ 16
        'rib_beneficiaire'      => [105, 20],  // Champ 17
        'nom_beneficiaire'      => [125, 30],  // Champ 18
        'reference_dossier'     => [155, 20],  // Champ 19 : Ref dossier paiement
        'code_enreg_comp'       => [175, 1],   // Champ 20 : Code enreg. complémentaire ← AJOUTÉ
        'nb_enreg_comp'         => [176, 2],   // Champ 21 : Nb enreg. complémentaires ← AJOUTÉ
        'motif_operation'       => [178, 45],  // Champ 22 : Motif ← CORRIGÉ (était 175)
        'date_compensation'     => [223, 8],   // Champ 23 : Date compensation initiale ← AJOUTÉ
        'motif_rejet'           => [231, 8],   // Champ 24 : Motif du rejet ← AJOUTÉ
        'situation_donneur'     => [239, 1],   // Champ 25 : Situation donneur ← CORRIGÉ (était 220)
        'type_compte'           => [240, 1],   // Champ 26 : Type compte donneur ← CORRIGÉ (était 221)
        'nature_compte'         => [241, 1],   // Champ 27 : Nature compte ← CORRIGÉ (était 222)
        'existence_dossier'     => [242, 1],   // Champ 28 : Existence dossier change ← CORRIGÉ (était 223)
        'zone_libre'            => [243, 37],  // Champ 29 : Zone libre ← CORRIGÉ (était 224)
    ];

    // ──────────────────────────────────────────────────────────────
    // PRÉLÈVEMENT (type 20) — 200 caractères
    // Spec SIBTEL p.40 — 100% correct dans le code précédent
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_DETAIL_PRELEVEMENT = [
        'montant'               => [28, 15],  // Champ 10
        'numero_prelevement'    => [43, 7],   // Champ 11
        'rib_payeur'            => [50, 20],  // Champ 12
        'code_institution_dest' => [70, 2],   // Champ 13
        'code_centre_dest'      => [72, 3],   // Champ 14
        'rib_creancier'         => [75, 20],  // Champ 15
        'code_emetteur'         => [95, 6],   // Champ 16 : Code national émetteur
        'ref_contrat'           => [101, 20], // Champ 17 : Ref contrat domiciliation
        'libelle_prelevement'   => [121, 50], // Champ 18
        'date_compensation'     => [171, 8],  // Champ 19
        'motif_rejet'           => [179, 8],  // Champ 20
        'date_echeance'         => [187, 8],  // Champ 21
        'zone_libre'            => [195, 7],  // Champ 22
    ];

    // ──────────────────────────────────────────────────────────────
    // CHÈQUE PRÉSENTÉ 1ÈRE FOIS (type 30) — 160 caractères
    // Spec SIBTEL p.42
    // CORRECTIONS : suppression date_preaviss, montant_provision, code_devise_position
    //               ajout lieu_emission, situation_beneficiaire, nature_compte corrects
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_DETAIL_CHEQUE_30 = [
        'montant'                => [28, 15],  // Champ 10
        'numero_cheque'          => [43, 7],   // Champ 11
        'rib_tireur'             => [50, 20],  // Champ 12
        'code_institution_dest'  => [70, 2],   // Champ 13
        'code_centre_dest'       => [72, 3],   // Champ 14
        'rib_beneficiaire'       => [75, 20],  // Champ 15
        'nom_beneficiaire'       => [95, 30],  // Champ 16
        'date_emission'          => [125, 8],  // Champ 17
        'lieu_emission'          => [133, 1],  // Champ 18 ← CORRIGÉ (existait pas avant)
        'situation_beneficiaire' => [134, 1],  // Champ 19 ← AJOUTÉ
        'nature_compte'          => [135, 1],  // Champ 20 ← AJOUTÉ
        'motif_rejet'            => [136, 8],  // Champ 21 ← CORRIGÉ position (était 159)
        'zone_libre'             => [144, 18], // Champ 22 ← CORRIGÉ (était [167,25])
    ];

    // ──────────────────────────────────────────────────────────────
    // CHÈQUE CNP — PAIEMENT PARTIEL (type 31) — 160 caractères
    // Spec SIBTEL p.44
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_DETAIL_CHEQUE_31 = [
        'montant'                 => [28, 15],  // Champ 10
        'numero_cheque'           => [43, 7],   // Champ 11
        'rib_tireur'              => [50, 20],  // Champ 12
        'code_institution_dest'   => [70, 2],   // Champ 13
        'code_centre_dest'        => [72, 3],   // Champ 14
        'rib_beneficiaire'        => [75, 20],  // Champ 15
        'date_emission'           => [95, 8],   // Champ 16
        'lieu_emission'           => [103, 1],  // Champ 17
        'date_cnp'                => [104, 8],  // Champ 18 : Date du CNP
        'numero_cnp'              => [112, 4],  // Champ 19
        'code_devise_position'    => [116, 3],  // Champ 20
        'montant_reclame'         => [119, 15], // Champ 21 : Montant reclame
        'montant_interets'        => [134, 15], // Champ 22 : Montant interets AJOUTE
        'motif_rejet'             => [149, 8],  // Champ 23 CORRIGE
        'zone_libre'              => [157, 3],  // Champ 24 CORRIGE
    ];

    // ──────────────────────────────────────────────────────────────
    // CHÈQUE ARP — APRÈS RÉGULARISATION (type 32) — 160 caractères
    // Spec SIBTEL p.45
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_DETAIL_CHEQUE_32 = [
        'montant'                => [28, 15],  // Champ 10
        'numero_cheque'          => [43, 7],   // Champ 11
        'rib_tireur'             => [50, 20],  // Champ 12
        'code_institution_dest'  => [70, 2],   // Champ 13
        'code_centre_dest'       => [72, 3],   // Champ 14
        'rib_beneficiaire'       => [75, 20],  // Champ 15
        'date_emission'          => [95, 8],   // Champ 16
        'lieu_emission'          => [103, 1],  // Champ 17
        'date_cnp'               => [104, 8],  // Champ 18
        'numero_cnp'             => [112, 4],  // Champ 19
        'code_devise_position'   => [116, 3],  // Champ 20
        'montant_reclame'        => [119, 15], // Champ 21 : Montant réclamé
        'montant_interets'       => [134, 15], // Champ 22 : Montant régularisé intérêts
        'motif_rejet'            => [149, 8],  // Champ 23
        'zone_libre'             => [157, 5],  // Champ 24
    ];

    // ──────────────────────────────────────────────────────────────
    // CNP CERTIFICAT NON-PAIEMENT (type 82) — 350 caractères
    // Spec SIBTEL p.49-50 — identique structure CNP complète
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_DETAIL_CHEQUE_82 = [
        'montant'                 => [28, 15],  // Champ 11
        'numero_cheque'           => [43, 7],   // Champ 12
        'rib_tireur'              => [50, 20],  // Champ 13
        'code_institution_dest'   => [70, 2],   // Champ 14
        'code_centre_dest'        => [72, 3],   // Champ 15
        'rib_beneficiaire'        => [75, 20],  // Champ 16
        'date_emission'           => [95, 8],   // Champ 17
        'lieu_emission'           => [103, 1],  // Champ 18
        'date_etablissement_cnp'  => [104, 8],  // Champ 19
        'numero_cnp'              => [112, 4],  // Champ 20
        'date_presentation'       => [116, 8],  // Champ 21
        'date_preaviss'           => [124, 8],  // Champ 22
        'montant_provision'       => [132, 15], // Champ 23
        'date_delivrance'         => [147, 8],  // Champ 24
        'motif_rejet'             => [155, 8],  // Champ 25
        'nb_enreg_comp'           => [163, 2],  // Champ 26
        'signature_electronique'  => [165, 128],// Champ 27
        'ref_cle_publique'        => [293, 14], // Champ 28
        'zone_libre'              => [307, 43], // Champ 29
    ];

    // ──────────────────────────────────────────────────────────────
    // ARP ATTESTATION RECONSTITUTION PROVISION (type 83) — 160 car.
    // Spec SIBTEL p.51 (section 3.1.2.1.10)
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_DETAIL_CHEQUE_83 = [
        'montant'                => [28, 15],  // Champ 10
        'numero_cheque'          => [43, 7],   // Champ 11
        'rib_tireur'             => [50, 20],  // Champ 12
        'code_institution_dest'  => [70, 2],   // Champ 13
        'code_centre_dest'       => [72, 3],   // Champ 14
        'rib_beneficiaire'       => [75, 20],  // Champ 15
        'date_emission'          => [95, 8],   // Champ 16
        'lieu_emission'          => [103, 1],  // Champ 17
        'date_cnp'               => [104, 8],  // Champ 18
        'numero_cnp'             => [112, 4],  // Champ 19
        'code_devise_position'   => [116, 3],  // Champ 20
        'montant_regularise'     => [119, 15], // Champ 21 : Montant régularisé
        'montant_interets'       => [134, 15], // Champ 22 : Montant régularisé intérêts
        'zone_libre'             => [149, 13], // Champ 23
    ];

    // ──────────────────────────────────────────────────────────────
    // LETTRE DE CHANGE (types 40, 41, 42, 43) — 380 caractères
    // Spec SIBTEL p.54-55
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_DETAIL_LETTRE_CHANGE = [
        'montant'                => [28, 15],  // Champ 11
        'montant_interets'       => [43, 15],  // Champ 12
        'montant_frais_protest'  => [58, 15],  // Champ 13
        'numero_lettre_change'   => [73, 12],  // Champ 14
        'rib_tire'               => [85, 20],  // Champ 15
        'rib_tire_initial'       => [105, 20], // Champ 16
        'code_institution_dest'  => [125, 2],  // Champ 17
        'code_centre_dest'       => [127, 3],  // Champ 18
        'rib_cedant'             => [130, 20], // Champ 19
        'nom_cedant'             => [150, 30], // Champ 20
        'nom_tire'               => [180, 30], // Champ 21
        'ref_commerciales_tire'  => [210, 30], // Champ 22
        'ref_commerciales_tireur'=> [240, 30], // Champ 23 (was missing before)
        'code_acceptation'       => [270, 1],  // Champ 24 (était 240 — décalé)
        'code_endossement'       => [271, 1],  // Champ 25
        'date_echeance'          => [272, 8],  // Champ 26 (corrected offset)
        'date_echeance_initiale' => [280, 8],  // Champ 27
        'date_creation'          => [288, 8],  // Champ 28 (was 258)
        'lieu_creation'          => [296, 30], // Champ 29 (optional per spec)
        'code_ordre_payer'       => [326, 1],  // Champ 30
        'situation_cedant'       => [327, 1],  // Champ 31
        'nature_compte'          => [328, 1],  // Champ 32
        'date_compensation'      => [329, 8],  // Champ 33
        'motif_rejet'            => [337, 8],  // Champ 34
        'code_risque_bct'        => [345, 6],  // Champ 35
        'zone_libre'             => [351, 29], // Champ 36
    ];

    // ──────────────────────────────────────────────────────────────
    // PAPILLON (type 84) — 280 caractères
    // Spec SIBTEL p.52 (section 3.1.2.1.11.2)
    // ──────────────────────────────────────────────────────────────
    const CHAMPS_DETAIL_PAPILLON = [
        'montant'                 => [28, 15],  // Champ 11
        'numero_cheque'           => [43, 7],   // Champ 12
        'rib_tireur'              => [50, 20],  // Champ 13
        'code_institution_dest'   => [70, 2],   // Champ 14
        'code_centre_dest'        => [72, 3],   // Champ 15
        'rib_beneficiaire'        => [75, 20],  // Champ 16
        'date_emission'           => [95, 8],   // Champ 17
        'lieu_emission'           => [103, 1],  // Champ 18
        'date_etablissement'      => [104, 8],  // Champ 19 : Date établissement papillon
        'numero_papillon'         => [112, 4],  // Champ 20
        'motif_rejet'             => [116, 8],  // Champ 21 ← AJOUTÉ
        'nb_enreg_comp'           => [124, 2],  // Champ 22 ← AJOUTÉ
        'zone_libre'              => [126, 154],// Champ 23
    ];

    // ──────────────────────────────────────────────────────────────
    // PARSE PRINCIPAL
    // ──────────────────────────────────────────────────────────────
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
            'assignations'    => [],
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

            // Déterminer la longueur selon le type détecté dans la ligne
            $ligneTemp = str_pad($ligne, max($longueur, 28));
            $codeValeurLigne = trim(substr($ligneTemp, 1, 2));
            if (array_key_exists($codeValeurLigne, self::LONGUEURS)) {
                $typeValeur = $codeValeurLigne;
                $longueur   = self::LONGUEURS[$typeValeur];
                $resultat['type_valeur'] = $typeValeur;
                $resultat['longueur']    = $longueur;
            }

            $ligne  = str_pad($ligne, $longueur);
            $entete = $this->extraireChamps($ligne, self::CHAMPS_ENTETE);
            $rang   = trim($entete['rang']);

            // Rang 00 = Enregistrement global
            if ($rang === '00') {
                $global = array_merge(
                    $entete,
                    $this->extraireChamps($ligne, self::CHAMPS_GLOBAL_COMMUN)
                );
                $global['ligne_numero'] = $numero + 1;
                $resultat['global']     = $global;

            // Rang 50 = Assignation postale (virement uniquement)
            } elseif ($rang === '50') {
                $assignation = array_merge($entete, [
                    'ligne_numero' => $numero + 1,
                ]);
                $resultat['assignations'][] = $assignation;

            // Rang 01-49 = Enregistrement détail
            } elseif (is_numeric($rang) && (int)$rang >= 1 && (int)$rang <= 49) {
                $champsDetail = $this->getChampsDetail($typeValeur);
                $detail       = array_merge(
                    $entete,
                    $this->extraireChamps($ligne, $champsDetail)
                );
                $detail['ligne_numero'] = $numero + 1;
                $detail['type_valeur']  = $typeValeur;
                $detail = $this->normaliserDetail($detail, $typeValeur);
                $resultat['details'][] = $detail;

            // Rang variable (ex: 01-20) = Enregistrement complémentaire
            } elseif (is_numeric($rang) && (int)$rang > 50) {
                $resultat['complementaires'][] = [
                    'rang'         => $rang,
                    'ligne_numero' => $numero + 1,
                    'contenu'      => $ligne,
                ];

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

    // ──────────────────────────────────────────────────────────────
    // SÉLECTION DES CHAMPS PAR TYPE DE VALEUR
    // ──────────────────────────────────────────────────────────────
    private function getChampsDetail(string $typeValeur): array
    {
        return match($typeValeur) {
            '20'        => self::CHAMPS_DETAIL_PRELEVEMENT,
            '30'        => self::CHAMPS_DETAIL_CHEQUE_30,
            '31'        => self::CHAMPS_DETAIL_CHEQUE_31,
            '32'        => self::CHAMPS_DETAIL_CHEQUE_32,
            '33'        => self::CHAMPS_DETAIL_CHEQUE_30,  // Structure identique au 30
            '40', '41', '42', '43' => self::CHAMPS_DETAIL_LETTRE_CHANGE,
            '82'        => self::CHAMPS_DETAIL_CHEQUE_82,  // CNP complet 350 car.
            '83'        => self::CHAMPS_DETAIL_CHEQUE_83,  // ARP 160 car.
            '84'        => self::CHAMPS_DETAIL_PAPILLON,
            default     => self::CHAMPS_DETAIL_VIREMENT,   // 10 et autres
        };
    }

    // ──────────────────────────────────────────────────────────────
    // NORMALISATION — Uniformise les noms de champs pour le reste
    // du pipeline (XmlTransformerService attend rib_donneur, etc.)
    // ──────────────────────────────────────────────────────────────
    private function normaliserDetail(array $detail, string $typeValeur): array
    {
        // Montant : déjà en millimes → diviser par 1000 pour obtenir TND
        $detail['montant'] = $this->formatMontant($detail['montant'] ?? '0');

        switch ($typeValeur) {

            case '20': // Prélèvement
                $detail['rib_donneur']      = $detail['rib_payeur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['rib_beneficiaire'] = $detail['rib_creancier'] ?? '';
                $detail['nom_beneficiaire'] = '';
                $detail['motif_operation']  = $detail['libelle_prelevement'] ?? '';
                $detail['numero_virement']  = $detail['numero_prelevement'] ?? '0';
                break;

            case '30': // Chèque présenté 1ère fois
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'CHEQUE PRESENTATION';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                break;

            case '31': // CNP partiel
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'CNP - PAIEMENT PARTIEL';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                if (!empty($detail['montant_reclame'])) {
                    $detail['montant_reclame'] = $this->formatMontant($detail['montant_reclame']);
                }
                break;

            case '32': // ARP
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'ARP - APRES REGULARISATION';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                if (!empty($detail['montant_reclame'])) {
                    $detail['montant_reclame'] = $this->formatMontant($detail['montant_reclame']);
                }
                if (!empty($detail['montant_interets'])) {
                    $detail['montant_interets'] = $this->formatMontant($detail['montant_interets']);
                }
                break;

            case '33': // Chèque retour
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'CHEQUE RETOUR';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                break;

            case '40': case '41': case '42': case '43': // LDC
                $detail['rib_donneur']      = $detail['rib_tire'] ?? '';
                $detail['nom_donneur']      = $detail['nom_tire'] ?? '';
                $detail['rib_beneficiaire'] = $detail['rib_cedant'] ?? '';
                $detail['nom_beneficiaire'] = $detail['nom_cedant'] ?? '';
                $detail['motif_operation']  = 'LETTRE DE CHANGE';
                $detail['numero_virement']  = $detail['numero_lettre_change'] ?? '0';
                if (!empty($detail['montant_interets'])) {
                    $detail['montant_interets'] = $this->formatMontant($detail['montant_interets']);
                }
                if (!empty($detail['montant_frais_protest'])) {
                    $detail['montant_frais_protest'] = $this->formatMontant($detail['montant_frais_protest']);
                }
                break;

            case '82': // CNP complet
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'CNP - CERTIFICAT NON-PAIEMENT';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                if (!empty($detail['montant_provision'])) {
                    $detail['montant_provision'] = $this->formatMontant($detail['montant_provision']);
                }
                break;

            case '83': // ARP complet
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'ARP - ATTESTATION RECONSTITUTION';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                if (!empty($detail['montant_regularise'])) {
                    $detail['montant_regularise'] = $this->formatMontant($detail['montant_regularise']);
                }
                if (!empty($detail['montant_interets'])) {
                    $detail['montant_interets'] = $this->formatMontant($detail['montant_interets']);
                }
                break;

            case '84': // Papillon
                $detail['rib_donneur']      = $detail['rib_tireur'] ?? '';
                $detail['nom_donneur']      = '';
                $detail['motif_operation']  = 'PAPILLON';
                $detail['numero_virement']  = $detail['numero_cheque'] ?? '0';
                break;

            // default = Virement (10) — champs déjà au bon nom
        }

        return $detail;
    }

    // ──────────────────────────────────────────────────────────────
    // Détecte le type depuis le nom du fichier
    // Format SIBTEL : RR-CCC-TT-NN.ENV → partie index 2 = type
    // Ex: 26-999-10-21-reel.ENV → '10'
    // ──────────────────────────────────────────────────────────────
    private function detecterTypeDepuisNom(string $nomFichier): string
    {
        $parties = explode('-', strtoupper($nomFichier));
        if (isset($parties[2]) && is_numeric($parties[2])) {
            $type = $parties[2];
            if (array_key_exists($type, self::LONGUEURS)) {
                return $type;
            }
        }
        return '10'; // Défaut : virement
    }

    // ──────────────────────────────────────────────────────────────
    // Extrait les champs depuis une ligne à position fixe
    // ──────────────────────────────────────────────────────────────
    private function extraireChamps(string $ligne, array $champs): array
    {
        $donnees = [];
        foreach ($champs as $nom => [$debut, $longueur]) {
            $donnees[$nom] = trim(substr($ligne, $debut, $longueur));
        }
        return $donnees;
    }

    // ──────────────────────────────────────────────────────────────
    // Convertit un montant en millimes vers TND
    // Le stockage en base sera en TND (decimal 15,3)
    // NE PAS rediviser par 1000 dans Pacs004TransformerService !
    // ──────────────────────────────────────────────────────────────
    private function formatMontant(string $montant): float
    {
        $montant = trim(ltrim($montant, '0'));
        if (empty($montant)) return 0.0;
        // Les montants SIBTEL sont en millimes (ex: 000005000000 = 5000.000 TND)
        return (float)$montant / 1000;
    }
}
