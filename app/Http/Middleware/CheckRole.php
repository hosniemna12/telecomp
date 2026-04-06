<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        if (!in_array($userRole, $roles)) {
            // Rediriger vers dashboard avec message d'erreur
            return redirect()->route('dashboard')
                ->with('erreur_acces', 'Vous n\'avez pas les permissions pour accéder à cette page.');
        }

        return $next($request);
    }
}