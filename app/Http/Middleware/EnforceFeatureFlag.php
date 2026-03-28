<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\FeatureFlagService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce feature flags on routes.
 *
 * Usage in routes: ->middleware('feature:inventario')
 * Will return 403 if the feature is disabled for the current tenant.
 */
class EnforceFeatureFlag
{
    public function __construct(
        protected FeatureFlagService $features,
    ) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!tenant()) {
            return $next($request);
        }

        if (!$this->features->enabled($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Esta funcionalidad no está disponible en tu plan.',
                    'feature' => $feature,
                ], 403);
            }

            return response()->view('feature-disabled', [
                'feature' => $feature,
                'label' => FeatureFlagService::featureLabels()[$feature] ?? $feature,
                'plan' => tenant()->planRelation?->nombre ?? 'Sin plan asignado',
            ], 403);
        }

        return $next($request);
    }
}
