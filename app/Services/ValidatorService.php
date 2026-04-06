<?php

namespace App\Services;

use App\Contracts\ValidatorInterface;

class ValidatorService implements ValidatorInterface
{
    private array $erreurs = [];

    public function valider(array $donnees): bool
    {
        $this->erreurs = [];

        $global  = $donnees['global'] ?? null;
        $details = $donnees['details'] ?? [];

        if (!$global) {
            $this->erreurs[] = 'Enregistrement global manquant';
            return false;
        }

        foreach ($details as $index => $detail) {
            $rang = $index + 1;

            // Validation montant
            if (empty($detail['montant']) || (float)$detail['montant'] <= 0) {
                $this->erreurs[] = "Transaction $rang : montant invalide ou nul";
            }

            // Validation RIB donneur
            if (!empty($detail['rib_donneur'])) {
                $rib = preg_replace('/\s+/', '', $detail['rib_donneur']);
                if (strlen($rib) !== 20) {
                    $this->erreurs[] = "Transaction $rang : RIB donneur invalide (longueur incorrecte)";
                } elseif (!ctype_digit($rib)) {
                    $this->erreurs[] = "Transaction $rang : RIB donneur invalide (caracteres non numeriques)";
                }
            } else {
                $this->erreurs[] = "Transaction $rang : RIB donneur manquant";
            }

            // Validation RIB beneficiaire
            if (!empty($detail['rib_beneficiaire'])) {
                $rib = preg_replace('/\s+/', '', $detail['rib_beneficiaire']);
                if (strlen($rib) !== 20) {
                    $this->erreurs[] = "Transaction $rang : RIB beneficiaire invalide (longueur incorrecte)";
                } elseif (!ctype_digit($rib)) {
                    $this->erreurs[] = "Transaction $rang : RIB beneficiaire invalide (caracteres non numeriques)";
                }
            } else {
                $this->erreurs[] = "Transaction $rang : RIB beneficiaire manquant";
            }

            // Validation coherence donneur != beneficiaire
            if (!empty($detail['rib_donneur']) &&
                !empty($detail['rib_beneficiaire']) &&
                $detail['rib_donneur'] === $detail['rib_beneficiaire']) {
                $this->erreurs[] = "Transaction $rang : RIB donneur et beneficiaire identiques";
            }
        }

        return empty($this->erreurs);
    }

    public function getErreurs(): array
    {
        return $this->erreurs;
    }
}