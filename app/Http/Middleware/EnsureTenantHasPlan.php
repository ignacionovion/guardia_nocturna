<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantHasPlan
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (!$tenant) {
            return $next($request);
        }

        $centralTenant = Tenant::query()
            ->select(['id', 'plan_id'])
            ->with('planRelation:id,slug,nombre')
            ->find($tenant->id);

        $resolvedTenant = $centralTenant ?? $tenant;
        $resolvedTenant->loadMissing('planRelation');

        // Solo bloquear si no hay plan_id o la relación es inválida
        // Permitir acceso si el plan está expirado (para QR públicos)
        if (!$resolvedTenant->plan_id || !$resolvedTenant->planRelation) {
            Log::error('tenant_blocked', [
                'reason' => 'plan_missing',
                'tenant_id' => $resolvedTenant->id,
                'plan_id' => $resolvedTenant->plan_id,
                'path' => $request->path(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'tenant_plan_missing',
                    'message' => 'Tenant sin plan asignado. Sistema inconsistente.',
                ], 503);
            }

            abort(503, 'Tenant sin plan asignado. Sistema inconsistente.');
        }

        // No bloquear por expiración aquí - permitir QR públicos funcionar
        // La expiración se maneja en EnsureTenantHasPlanForApp

        return $next($request);
    }
}
