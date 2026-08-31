<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RecoveryLoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('ldap')->check() || Auth::guard('local')->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.recovery-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('username', $credentials['username'])
            ->where('auth_source', 'local')
            ->first();

        if (! $user || ! $user->is_active || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'username' => 'Credenciais de recuperação inválidas.',
            ])->onlyInput('username');
        }

        Auth::guard('local')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('dashboard'));
    }
}
