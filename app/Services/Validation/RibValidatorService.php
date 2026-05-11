<?php

namespace App\Services\Validation;

class RibValidatorService
{
    const BANQUES = [
        '01' => 'Banque Nationale Agricole (BNA)',
        '02' => 'Societe Tunisienne de Banque (STB)',
        '03' => 'Banque de l Habitat (BH)',
        '04' => 'Banque Internationale Arabe de Tunisie (BIAT)',
        '05' => 'Attijari Bank',
        '06' => 'Banque de Tunisie (BT)',
        '07' => 'Arab Tunisian Bank (ATB)',
        '08' => 'Union Bancaire pour le Commerce et l Industrie (UBCI)',
        '09' => 'Union Internationale de Banques (UIB)',
        '10' => 'Citibank Tunisie',
        '11' => 'Amen Bank',
        '14' => 'Banque Tuniso-Koweitienne (BTK)',
        '20' => 'Arab Banking Corporation (ABC)',
        '21' => 'Stusid Bank',
        '23' => 'Banque Zitouna',
        '24' => 'Al Baraka Bank',
        '25' => 'Qatar National Bank (QNB)',
        '26' => 'Wifak International Bank',
    ];

    public function valider(string $rib): array
    {
        $rib = preg_replace('/\s+/', '', $rib);

        if (strlen($rib) !== 20) {
            return [
                'valide' => false,
                'erreur' => 'Le RIB doit contenir exactement 20 chiffres (recu : ' . strlen($rib) . ')',
                'rib'    => $rib,
            ];
        }

        if (!ctype_digit($rib)) {
            return [
                'valide' => false,
                'erreur' => 'Le RIB ne doit contenir que des chiffres',
                'rib'    => $rib,
            ];
        }

        $codeBanque  = substr($rib, 0, 2);
        $codeAgence  = substr($rib, 2, 3);
        $numCompte   = substr($rib, 5, 13);
        $cleControle = substr($rib, 18, 2);
        $nomBanque   = self::BANQUES[$codeBanque] ?? null;
        $cleCalculee = $this->calculerCle($codeBanque, $codeAgence, $numCompte);
        $cleValide   = ($cleCalculee === $cleControle);

        return [
            'valide'       => $cleValide,
            'rib'          => $rib,
            'code_banque'  => $codeBanque,
            'nom_banque'   => $nomBanque ?? 'Banque inconnue (code: ' . $codeBanque . ')',
            'code_agence'  => $codeAgence,
            'num_compte'   => $numCompte,
            'cle_controle' => $cleControle,
            'cle_calculee' => $cleCalculee,
            'erreur'       => $cleValide ? null : 'Cle de controle invalide (attendu: ' . $cleCalculee . ', recu: ' . $cleControle . ')',
        ];
    }

    private function calculerCle(string $banque, string $agence, string $compte): string
    {
        $nombre = $banque . $agence . $compte . '00';
        $reste  = bcmod($nombre, '97');
        $cle    = 97 - (int)$reste;
        return str_pad($cle, 2, '0', STR_PAD_LEFT);
    }

    public function estValide(string $rib): bool
    {
        return $this->valider($rib)['valide'];
    }

    public function formater(string $rib): string
    {
        $rib = preg_replace('/\s+/', '', $rib);
        if (strlen($rib) !== 20) return $rib;
        return substr($rib, 0, 2) . ' ' . substr($rib, 2, 3) . ' ' . substr($rib, 5, 13) . ' ' . substr($rib, 18, 2);
    }
}