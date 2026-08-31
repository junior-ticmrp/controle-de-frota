<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CorporateLoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('ldap')->check() || Auth::guard('local')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.corporate-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $authenticated = Auth::guard('ldap')->attempt([
            'samaccountname' => $credentials['username'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'));

        if (! $authenticated) {
            return back()->withErrors([
                'username' => 'Credenciais inválidas ou usuário sem autorização para o sistema.',
            ])->onlyInput('username');
        }

        $user = Auth::guard('ldap')->user();

        if (! $user instanceof User || ! $user->is_active || $user->auth_source !== 'ldap') {
            Auth::guard('ldap')->logout();

            return back()->withErrors([
                'username' => 'Esta conta não está ativa para acesso corporativo.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('dashboard'));
    }
}
