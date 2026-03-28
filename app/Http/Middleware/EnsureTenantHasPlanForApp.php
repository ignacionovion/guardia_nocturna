<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantHasPlanForApp
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

        if ($resolvedTenant->expires_at && now()->greaterThan($resolvedTenant->expires_at)) {
            if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
                return $next($request);
            }

            Log::warning('tenant_blocked', [
                'reason' => 'plan_expired',
                'tenant_id' => $resolvedTenant->id,
                'expires_at' => $resolvedTenant->expires_at,
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'tenant_plan_expired',
                    'message' => 'El plan del tenant está expirado. Operación bloqueada.',
                ], 403);
            }

            abort(403, 'El plan del tenant está expirado. Operación bloqueada.');
        }

        return $next($request);
    }
}
