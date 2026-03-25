<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\FeatureFlagService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure a tenant has a specific feature enabled in their plan.
 * 
 * Usage: ->middleware('tenant.feature:inventario')
 */
class EnsureTenantFeatureEnabled
{
    public function __construct(
        protected FeatureFlagService $featureService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Only apply in tenant context
        $tenant = tenant();
        if (!$tenant) {
            return $next($request);
        }

        // Check if feature is enabled for this tenant
        if (!$this->featureService->enabled($feature)) {
            // Cargar la relación 'plan' si no está cargada
            $tenant->loadMissing('plan');

            Log::warning('Feature blocked for tenant.', [
                'tenant_id' => $tenant->id,
                'feature' => $feature,
                'plan_id' => $tenant->plan_id,
                'plan_slug' => $tenant->plan?->slug, // Acceder al slug desde la relación
                'request_uri' => $request->getRequestUri(),
            ]);

            // Return 403 with custom view
            return response()->view('errors.feature-disabled', [
                'feature' => $feature,
                'featureName' => $this->getFeatureName($feature),
            ], 403);
        }

        return $next($request);
    }

    /**
     * Get human-readable feature name
     */
    protected function getFeatureName(string $feature): string
    {
        $names = [
            'inventario' => 'Inventario de Materiales',
            'planilla' => 'Planilla y Asistencia',
            'preventiva' => 'Mantenimiento Preventivo',
            'emergencias' => 'Módulo de Emergencias',
            'reportes' => 'Reportes y Estadísticas',
            'calendario' => 'Calendario y Planificación',
            'now' => 'Guardia NOW',
            'voluntarios' => 'Gestión de Voluntarios',
            'dotaciones' => 'Gestión de Dotaciones',
            'api_access' => 'Acceso API',
            'webhooks' => 'Webhooks e Integraciones',
            'custom_branding' => 'Marca Personalizada',
        ];

        return $names[$feature] ?? ucfirst($feature);
    }
}
