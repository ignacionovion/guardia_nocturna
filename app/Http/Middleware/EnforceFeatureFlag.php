<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\PlanAccessDeniedException;
use App\Services\FeatureFlagService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to enforce feature flags on routes.
 *
 * Usage in routes: ->middleware('feature:inventario')
 * Redirige a la pantalla de upgrade si la feature no está habilitada.
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
            $tenant = tenant();
            $tenant?->loadMissing('planRelation');

            throw PlanAccessDeniedException::featureNotIncluded($feature, $tenant?->planRelation?->nombre);
        }

        return $next($request);
    }
}
