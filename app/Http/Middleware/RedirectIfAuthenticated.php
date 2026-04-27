<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        // Solo aplicar lógica de "ya autenticado" en el login del tenant (rutas con middleware `guest`).
        // Evita el síntoma de que, si `guest` se aplicara por error a otras URLs, toda la app
        // redirigiera de forma fija a `dashboard.live` en cada request.
        $isTenantLoginEntry = $request->routeIs('tenant.login')
            || ($request->is('/') && in_array($request->method(), ['GET', 'HEAD', 'POST'], true));

        if (! $isTenantLoginEntry) {
            return $next($request);
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Central guard → central dashboard
                if ($guard === 'central') {
                    return redirect('/admin');
                }
                
                $user = Auth::guard($guard)->user();
                if ($user && $user->role === 'guardia') {
                    $guardiaRoute = Route::has('guardia.dashboard')
                        ? 'guardia.dashboard'
                        : (Route::has('guardia') ? 'guardia' : 'dashboard.live');

                    return redirect()->route($guardiaRoute);
                }

                // Web guard (tenant) → dashboard principal de administración
                return redirect()->route('dashboard.live');
            }
        }

        return $next($request);
    }
}
