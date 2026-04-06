<?php

namespace App\Contracts;

interface ValidatorInterface
{
    /**
     * Valider les données parsées
     * Retourne true si valide, false sinon
     */
    public function valider(array $donnees): bool;

    /**
     * Récupérer les erreurs de validation
     */
    public function getErreurs(): array;
}