<?php

namespace App\Services;

use App\Models\TcFichier;
use App\Models\TcCommentaire;
use App\Models\TcNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ValidationService
{
    public function __construct(
        private AuditService $audit
    ) {}

    /**
     * Après upload par opérateur → statut EN_ATTENTE_VALIDATION
     * Notifier les superviseurs
     */
    public function soumettreValidation(TcFichier $fichier): void
    {
        $user = Auth::user();

        // Si admin → bypass validation → directement TRAITE
        if ($user->role === 'admin') {
            return;
        }

        // Opérateur → EN_ATTENTE_VALIDATION
        if ($user->role === 'operateur') {
            $fichier->update([
                'statut'      => 'EN_ATTENTE_VALIDATION',
                'uploaded_by' => $user->id,
            ]);

            // Notifier tous les superviseurs et admins
            TcNotification::notifierSuperviseurs(
                'Nouveau fichier à valider',
                "L'opérateur {$user->name} a soumis le fichier {$fichier->nom_fichier} — " .
                "{$fichier->nb_transactions} transactions — en attente de votre validation.",
                $fichier->id
            );

            $this->audit->log('SOUMISSION_VALIDATION', 'FICHIERS',
                "Fichier soumis pour validation : {$fichier->nom_fichier}",
                [], ['statut' => 'EN_ATTENTE_VALIDATION']
            );
        }
    }

    /**
     * Superviseur ou Admin valide le fichier → génération XML autorisée
     */
    public function valider(TcFichier $fichier, string $commentaire = ''): void
    {
        $user = Auth::user();

        $fichier->update([
            'statut'           => 'VALIDE',
            'valide_par'       => $user->id,
            'date_validation'  => now(),
        ]);

        // Ajouter commentaire si fourni
        if (!empty($commentaire)) {
            TcCommentaire::create([
                'fichier_id' => $fichier->id,
                'user_id'    => $user->id,
                'contenu'    => $commentaire,
                'type'       => 'VALIDATION',
            ]);
        }

        // Notifier l'opérateur
        if ($fichier->uploaded_by) {
            TcNotification::notifierOperateur(
                $fichier->uploaded_by,
                'Fichier validé ✓',
                "Votre fichier {$fichier->nom_fichier} a été validé par {$user->name}. " .
                "La génération XML peut maintenant être lancée.",
                $fichier->id
            );
        }

        $this->audit->log('VALIDATION', 'FICHIERS',
            "Fichier validé par {$user->name} : {$fichier->nom_fichier}",
            ['statut' => 'EN_ATTENTE_VALIDATION'],
            ['statut' => 'VALIDE', 'valide_par' => $user->id]
        );
    }

    /**
     * Superviseur rejette le fichier
     */
    public function rejeter(TcFichier $fichier, string $motif): void
    {
        $user = Auth::user();

        $fichier->update([
            'statut'             => 'REJETE_VALIDATION',
            'valide_par'         => $user->id,
            'date_validation'    => now(),
            'commentaire_rejet'  => $motif,
        ]);

        // Commentaire automatique
        TcCommentaire::create([
            'fichier_id' => $fichier->id,
            'user_id'    => $user->id,
            'contenu'    => "Rejet : {$motif}",
            'type'       => 'REJET',
        ]);

        // Notifier l'opérateur
        if ($fichier->uploaded_by) {
            TcNotification::notifierOperateur(
                $fichier->uploaded_by,
                'Fichier rejeté ✗',
                "Votre fichier {$fichier->nom_fichier} a été rejeté par {$user->name}. Motif : {$motif}",
                $fichier->id
            );
        }

        $this->audit->log('REJET_VALIDATION', 'FICHIERS',
            "Fichier rejeté par {$user->name} : {$fichier->nom_fichier} — Motif : {$motif}",
            ['statut' => 'EN_ATTENTE_VALIDATION'],
            ['statut' => 'REJETE_VALIDATION']
        );
    }

    /**
     * Ajouter un commentaire sur un fichier
     */
    public function commenter(TcFichier $fichier, string $contenu): TcCommentaire
    {
        $commentaire = TcCommentaire::create([
            'fichier_id' => $fichier->id,
            'user_id'    => Auth::id(),
            'contenu'    => $contenu,
            'type'       => 'COMMENTAIRE',
        ]);

        $this->audit->log('COMMENTAIRE', 'FICHIERS',
            "Commentaire ajouté sur {$fichier->nom_fichier}",
            [], ['contenu' => substr($contenu, 0, 100)]
        );

        return $commentaire;
    }

    /**
     * Marquer les notifications comme lues
     */
    public function marquerLu(int $notificationId): void
    {
        \App\Models\TcNotification::where('id', $notificationId)
            ->where('user_id', Auth::id())
            ->update(['lu' => 1]);
    }

    /**
     * Compter les notifications non lues de l'utilisateur connecté
     */
    public static function countNonLues(): int
    {
        if (!Auth::check()) return 0;
        return \App\Models\TcNotification::where('user_id', Auth::id())
            ->where('lu', 0)
            ->count();
    }
}
