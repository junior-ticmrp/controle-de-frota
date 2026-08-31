<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFleetAccess
{
    /**
     * Autoriza usuários provenientes dos guards LDAP e local.
     *
     * Exemplos de rota:
     *   middleware('fleet.access')                 // qualquer identidade ativa
     *   middleware('fleet.access:supervisor')      // somente supervisor operacional
     *   middleware('fleet.access:supervisor')      // gestão cadastral operacional
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = null;
        $guardName = null;

        foreach (['ldap', 'local'] as $candidate) {
            if (Auth::guard($candidate)->check()) {
                $user = Auth::guard($candidate)->user();
                $guardName = $candidate;
                break;
            }
        }

        if (! $user || ! $user->is_active) {
            Auth::guard('ldap')->logout();
            Auth::guard('local')->logout();

            return redirect()
                ->route('login')
                ->withErrors(['username' => 'Sua sessão não está ativa. Faça login novamente.']);
        }

        $allowedRoles = array_values(array_filter($roles));

        if ($allowedRoles !== [] && ! in_array($user->role, $allowedRoles, true)) {
            abort(403, 'Seu papel não possui permissão para acessar este módulo.');
        }

        // Evita repetir a detecção do guard nos controllers e nas views.
        $request->attributes->set('fleet.user', $user);
        $request->attributes->set('fleet.guard', $guardName);

        return $next($request);
    }
}
