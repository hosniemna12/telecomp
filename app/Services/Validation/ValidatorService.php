<?php

namespace App\Services\Validation;

use App\Contracts\ValidatorInterface;

class ValidatorService implements ValidatorInterface
{
    private array $erreurs = [];
    private RibValidatorService $ribValidator;

    public function __construct(RibValidatorService $ribValidator)
    {
        $this->ribValidator = $ribValidator;
    }

    public function valider(array $donnees): bool
    {
        $this->erreurs = [];
        $global  = $donnees['global']  ?? null;
        $details = $donnees['details'] ?? [];

        if (!$global || !is_array($global)) {
            $this->erreurs[] = 'Enregistrement global manquant';
            return false;
        }
        if (!is_array($details) || empty($details)) {
            $this->erreurs[] = 'Au moins une transaction requise';
            return false;
        }

        $typeValeur = trim($global['code_valeur'] ?? '10');

        foreach ($details as $index => $detail) {
            $rang = $index + 1;
            if (!is_array($detail)) { continue; }

            $montant = $detail['montant'] ?? null;
            if ($montant === null || !is_numeric($montant) || (float)$montant <= 0) {
                $this->erreurs[] = "Transaction $rang : montant invalide";
            }

            $this->validerRib($detail['rib_donneur'] ?? '', "Transaction $rang : RIB donneur", true);

            // Le RIB bénéficiaire n'est pas obligatoire pour le type 33 (Chèque retour)
            $benef_obl = ($typeValeur !== '33');
            $this->validerRib($detail['rib_beneficiaire'] ?? '', "Transaction $rang : RIB beneficiaire", $benef_obl);

            $this->validerParType($detail, $rang, $typeValeur);
        }

        return empty($this->erreurs);
    }

    private function validerParType(array $detail, int $rang, string $typeValeur): void
    {
        switch ($typeValeur) {
            case '20':
                $ce = trim($detail['code_emetteur'] ?? '');
                if (empty($ce)) {
                    $this->erreurs[] = "Transaction $rang : code_emetteur manquant";
                } elseif (strlen(preg_replace('/\D/', '', $ce)) !== 6) {
                    $this->erreurs[] = "Transaction $rang : code_emetteur doit faire 6 chiffres";
                }
                if (empty(trim($detail['ref_contrat'] ?? ''))) {
                    $this->erreurs[] = "Transaction $rang : ref_contrat manquant";
                }
                // Valider RIB créancier pour prélèvement
                $this->validerRib($detail['rib_creancier'] ?? '', "Transaction $rang : RIB creancier", true);
                break;
            case '30':
                if (empty(trim($detail['date_emission'] ?? ''))) {
                    $this->erreurs[] = "Transaction $rang : date_emission manquante";
                }
                break;
            case '40': case '41': case '42': case '43':
                if (empty(trim($detail['date_echeance'] ?? ''))) {
                    $this->erreurs[] = "Transaction $rang : date_echeance obligatoire";
                }
                break;
            case '84':
                if (empty(trim($detail['date_etablissement'] ?? ''))) {
                    $this->erreurs[] = "Transaction $rang : date_etablissement manquante";
                }
                break;
        }
    }

    private function validerRib(string $rib, string $label, bool $obligatoire): void
    {
        $rib = trim($rib);
        if (empty($rib)) {
            if ($obligatoire) { $this->erreurs[] = "$label manquant"; }
            return;
        }
        $chiffres = preg_replace('/\D/', '', $rib);
        if (strlen($chiffres) !== 20) {
            $this->erreurs[] = "$label invalide (" . strlen($chiffres) . " chiffres au lieu de 20)";
            return;
        }
        try {
            $result = $this->ribValidator->valider($chiffres);
            if (!($result['valide'] ?? false)) {
                $this->erreurs[] = "$label invalide — " . ($result['erreur'] ?? 'Cle invalide');
            }
        } catch (\Exception $e) {
            $this->erreurs[] = "$label : erreur validation";
        }
    }

    public function getErreurs(): array { return $this->erreurs; }
}
