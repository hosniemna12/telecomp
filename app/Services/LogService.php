<?php

namespace App\Services;

use App\Contracts\LoggerInterface;
use App\Models\TcLogsTraitement;

class LogService implements LoggerInterface
{
    public function info(int $fichierId, string $etape, string $message, array $contexte = []): void
    {
        $this->enregistrer($fichierId, $etape, 'INFO', $message, $contexte);
    }

    public function warning(int $fichierId, string $etape, string $message, array $contexte = []): void
    {
        $this->enregistrer($fichierId, $etape, 'WARNING', $message, $contexte);
    }

    public function erreur(int $fichierId, string $etape, string $message, array $contexte = []): void
    {
        $this->enregistrer($fichierId, $etape, 'ERROR', $message, $contexte);
    }
    private function enregistrer(int $fichierId, string $etape, string $niveau, string $message, array $contexte): void
{
    TcLogsTraitement::create([
        'fichier_id'       => $fichierId,
        'etape'            => $etape,
        'niveau'           => $niveau,
        'message'          => $message,
        'donnees_contexte' => !empty($contexte) ? json_encode($contexte) : null,
        'created_at'       => now(),
    ]);
}
}