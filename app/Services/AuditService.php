<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

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
                'description'   => $description,
                'ip_address'    => request()->ip(),
                'donnees_avant' => !empty($donneesAvant) ? json_encode($donneesAvant) : null,
                'donnees_apres' => !empty($donneesApres) ? json_encode($donneesApres) : null,
                'statut'        => $statut,
                'created_at'    => now(),
            ]);
        } catch (\Exception $e) {
            // Ne pas bloquer l application si audit echoue
        }
    }

    public function loginSuccess(string $email): void
    {
        AuditTrail::create([
            'user_email' => $email,
            'action'     => 'LOGIN',
            'module'     => 'AUTH',
            'description'=> "Connexion reussie : $email",
            'ip_address' => request()->ip(),
            'statut'     => 'SUCCESS',
            'created_at' => now(),
        ]);
    }

    public function loginFailed(string $email): void
    {
        AuditTrail::create([
            'user_email' => $email,
            'action'     => 'LOGIN',
            'module'     => 'AUTH',
            'description'=> "Tentative de connexion echouee : $email",
            'ip_address' => request()->ip(),
            'statut'     => 'FAILED',
            'created_at' => now(),
        ]);
    }
}