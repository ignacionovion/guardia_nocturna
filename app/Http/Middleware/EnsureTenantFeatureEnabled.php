<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\PlanAccessDeniedException;
use App\Services\FeatureFlagService;
use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        if (! $this->featureService->enabled($feature)) {
            $plan = PlanService::planForTenant($tenant);

            Log::warning('Feature blocked for tenant.', [
                'tenant_id' => $tenant->id,
                'feature' => $feature,
                'plan_id' => $tenant->plan_id,
                'plan_slug' => $plan?->slug,
                'request_uri' => $request->getRequestUri(),
            ]);

            throw PlanAccessDeniedException::featureNotIncluded($feature, $plan?->nombre);
        }

        return $next($request);
    }
}
