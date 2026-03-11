<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Central\ImpersonationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateCallbackController extends Controller
{
    /**
     * Handle impersonation callback from central panel.
     */
    public function callback(Request $request)
    {
        $token = $request->get('token');

        if (!$token) {
            return redirect('/login')->with('error', 'Token de impersonación inválido.');
        }

        $data = ImpersonationController::validateToken($token);

        if (!$data) {
            return redirect('/login')->with('error', 'Token de impersonación expirado o inválido.');
        }

        // Verify we're in the correct tenant
        $currentTenant = tenant();
        if (!$currentTenant || $currentTenant->id !== $data['tenant_id']) {
            return redirect('/login')->with('error', 'Tenant incorrecto para esta impersonación.');
        }

        // Find and login as the user
        $user = \App\Models\User::find($data['user_id']);

        if (!$user) {
            return redirect('/login')->with('error', 'Usuario no encontrado.');
        }

        // Login as the user
        Auth::guard('web')->login($user);

        return redirect('/dashboard')->with('success', "Impersonando como {$user->name}. Usa el banner superior para salir.");
    }
}
