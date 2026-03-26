<?php

namespace App\Http\Controllers;

use App\Models\Guardia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        // DEBUG: Verify tenant context before authentication
        \Log::info('=== AuthController@login START ===', [
            'tenant_initialized' => tenancy()->initialized,
            'tenant_id' => tenant('id'),
            'db_connection' => \DB::connection()->getDatabaseName(),
            'host' => $request->getHost(),
        ]);

        // Use explicit 'web' guard for tenant authentication
        if (Auth::guard('web')->attempt($credentials)) {
            // Get authenticated user from 'web' guard
            $user = Auth::guard('web')->user();
            
            \Log::info('User authenticated', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'tenant_id' => tenant('id'),
            ]);
            
            // Bloquear roles sin acceso al sistema
            if (in_array($user->role, ['bombero', 'jefe_guardia', 'inventario'], true)) {
                Auth::guard('web')->logout();
                \Log::warning('User blocked by role', ['user_id' => $user->id, 'role' => $user->role]);
                return back()->withErrors([
                    'username' => 'Su cuenta no tiene permisos para acceder al sistema.',
                ])->onlyInput('username');
            }

            // Validar guardia activa antes de permitir login
            if ($user->role === 'guardia') {
                if (!$user->guardia_id) {
                    Auth::guard('web')->logout();
                    \Log::warning('Guardia user has no guardia_id', ['user_id' => $user->id]);
                    return back()->withErrors([
                        'username' => 'Tu cuenta de guardia no está asociada a ninguna guardia.',
                    ])->onlyInput('username');
                }

                $guardia = Guardia::find($user->guardia_id);

                if (!$guardia || !$guardia->is_active_week) {
                    Auth::guard('web')->logout();
                    \Log::warning('Guardia not active', ['user_id' => $user->id, 'guardia_id' => $user->guardia_id]);
                    return back()->withErrors([
                        'username' => 'Tu guardia no está activa en este momento. Contacta al capitán.',
                    ])->onlyInput('username');
                }
            }

            // Force clean login to ensure session persistence
            Auth::guard('web')->login($user);

            // Regenerate session AFTER login is confirmed
            $request->session()->regenerate();

            \Log::info('=== AuthController@login SUCCESS ===', [
                'user_id' => $user->id,
                'tenant_id' => tenant('id'),
                'session_id' => $request->session()->getId(),
                'auth_check' => Auth::guard('web')->check(),
            ]);

            return redirect('/dashboard');
        }

        \Log::warning('Authentication failed', [
            'username' => $credentials['username'],
            'tenant_id' => tenant('id'),
        ]);

        return back()->withErrors([
            'username' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
