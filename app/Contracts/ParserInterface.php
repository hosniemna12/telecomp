<?php

namespace App\Contracts;

interface ParserInterface
{
    /**
     * Parser un fichier ENV/PAK T24
     * Retourne un tableau structuré des enregistrements
     */
    public function parse(string $cheminFichier): array;

    /**
     * Vérifier si le fichier est supporté par ce parser
     */
    public function supporte(string $cheminFichier): bool;
}