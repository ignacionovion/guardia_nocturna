<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cierra sesión si el central_admin fue desactivado con sesión aún válida.
 */
final class EnsureCentralAdminIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('central')->user();

        if ($user !== null && ! $user->activo) {
            Auth::guard('central')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->to('/login')
                ->withErrors(['username' => 'Tu cuenta fue desactivada.']);
        }

        return $next($request);
    }
}
