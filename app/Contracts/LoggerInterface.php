<?php

namespace App\Contracts;

interface LoggerInterface
{
    public function info(int $fichierId, string $etape, string $message, array $contexte = []): void;

    public function warning(int $fichierId, string $etape, string $message, array $contexte = []): void;

    public function erreur(int $fichierId, string $etape, string $message, array $contexte = []): void;
}