<?php

namespace App\Services\Notification;

use App\Models\TcFichier;
use App\Models\TcNotification;
use App\Models\User;

/**
 * Service de dispatch des notifications metier liees au workflow fichier.
 *
 * Centralise la logique de notification des acteurs (superviseurs, admins)
 * lors des transitions d'etat d'un fichier (upload, validation, rejet).
 *
 * Conçu pour etre etendu : on peut ajouter des methodes pour les autres
 * evenements (notifier operateur d'une validation, etc.) sans toucher
 * a l'orchestrateur.
 */
class NotificationDispatchService
{
    /**
     * Notifie les superviseurs et admins qu'un nouveau fichier
     * a ete soumis par un operateur et attend validation.
     *
     * No-op si l'auteur n'est pas un operateur ou si le fichier
     * n'est pas en attente de validation.
     */
    public function notifierNouveauFichier(
        TcFichier $fichier,
        User $auteur,
        int $nbValides,
        string $statut
    ): void {
        // Garde : on ne notifie que pour les uploads operateur en attente
        if ($auteur->role !== 'operateur' || $statut !== 'EN_ATTENTE_VALIDATION') {
            return;
        }

        $superviseurs = User::whereIn('role', ['superviseur', 'admin'])->get();

        foreach ($superviseurs as $sup) {
            TcNotification::create([
                'user_id'    => $sup->id,
                'titre'      => 'Nouveau fichier a valider',
                'message'    => "L'operateur {$auteur->name} a soumis {$fichier->nom_fichier} — {$nbValides} transactions valides.",
                'type'       => 'UPLOAD',
                'fichier_id' => $fichier->id,
            ]);
        }
    }
}