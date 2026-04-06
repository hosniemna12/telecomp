<?php

namespace App\Contracts;

interface TransformerInterface
{
    /**
     * Transformer les données vers XML ISO 20022
     * Retourne le contenu XML sous forme de string
     */
    public function transformer(array $donnees): string;

    /**
     * Retourner le type de message ISO 20022
     * ex: pacs.008.001.10, pacs.003.001.09
     */
    public function getTypeMessage(): string;
}