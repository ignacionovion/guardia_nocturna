<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\PlanAccessDeniedException;
use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanAccess
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = tenant();

        if (!$tenant) {
            throw PlanAccessDeniedException::organizationNotResolved();
        }

        $plan = PlanService::planForTenant($tenant);

        if (! $plan) {
            throw PlanAccessDeniedException::noPlanAssigned();
        }

        if (!$plan->hasFeature($feature)) {
            throw PlanAccessDeniedException::featureNotIncluded($feature, $plan->nombre);
        }

        return $next($request);
    }
}
