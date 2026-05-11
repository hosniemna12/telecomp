<?php

namespace App\Services\Audit;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

/**
 * AuditService — Journalisation des actions utilisateurs
 *
 * CORRIGÉ :
 *  - Champ statut unifié : 'statut' dans AuditTrail (pas 'statut_action')
 *  - loginSuccess et loginFailed utilisent le même champ 'statut'
 *  - user_id nullable pour les actions sans authentification
 */
class AuditService
{
    public function log(
        string $action,
        string $module,
        string $description,
        array  $donneesAvant = [],
        array  $donneesApres = [],
        string $statut = 'SUCCESS'
    ): void {
        try {
            AuditTrail::create([
                'user_id'       => Auth::id(),
                'user_email'    => Auth::user()?->email,
                'action'        => $action,
                'module'        => $module,
                'description'   => mb_substr($description, 0, 500), // sécurité longueur
                'ip_address'    => request()->ip(),
                'donnees_avant' => !empty($donneesAvant) ? json_encode($donneesAvant, JSON_UNESCAPED_UNICODE) : null,
                'donnees_apres' => !empty($donneesApres) ? json_encode($donneesApres, JSON_UNESCAPED_UNICODE) : null,
                'statut'        => $statut, // CORRIGÉ : 'statut' (pas 'statut_action')
            ]);
        } catch (\Exception $e) {
            // Ne pas bloquer l'application si l'audit échoue
        }
    }

    public function loginSuccess(string $email): void
    {
        try {
            AuditTrail::create([
                'user_email'  => $email,
                'action'      => 'LOGIN',
                'module'      => 'AUTH',
                'description' => "Connexion réussie : {$email}",
                'ip_address'  => request()->ip(),
                'statut'      => 'SUCCESS', // CORRIGÉ : 'statut'
            ]);
        } catch (\Exception $e) {
            // Ne pas bloquer le login si l'audit échoue
        }
    }

    public function loginFailed(string $email): void
    {
        try {
            AuditTrail::create([
                'user_email'  => $email,
                'action'      => 'LOGIN',
                'module'      => 'AUTH',
                'description' => "Tentative de connexion échouée : {$email}",
                'ip_address'  => request()->ip(),
                'statut'      => 'FAILED', // CORRIGÉ : 'statut'
            ]);
        } catch (\Exception $e) {
            // Ne pas bloquer
        }
    }
}