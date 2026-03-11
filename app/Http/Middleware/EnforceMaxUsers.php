<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\FeatureFlagService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce max_users plan limit.
 *
 * Blocks user creation when the tenant has reached its user limit.
 * Only applies to POST routes that create users.
 */
class EnforceMaxUsers
{
    public function __construct(
        protected FeatureFlagService $features,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!tenant()) {
            return $next($request);
        }

        $maxUsers = $this->features->get('max_users');

        // -1 means unlimited
        if ($maxUsers === null || $maxUsers === -1) {
            return $next($request);
        }

        $currentUsers = \App\Models\User::count();

        if ($currentUsers >= (int) $maxUsers) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => "Límite de usuarios alcanzado ({$maxUsers}). Actualiza tu plan para agregar más.",
                ], 403);
            }

            return back()->with('error', "Has alcanzado el límite de {$maxUsers} usuarios de tu plan. Contacta al administrador para actualizar tu plan.");
        }

        return $next($request);
    }
}
