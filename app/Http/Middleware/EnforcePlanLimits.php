<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\PlanAccessDeniedException;
use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePlanLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $resourceType): Response
    {
        // Check if we're in a tenant context
        if (!tenant()) {
            return $next($request);
        }
        
        // For POST requests (creating resources), check if limit would be exceeded
        if ($request->isMethod('post') || $request->isMethod('put')) {
            if (PlanService::exceedsLimit($resourceType, 1)) {
                $limit = PlanService::getLimit($resourceType);
                if ($limit === null) {
                    return $next($request);
                }

                throw PlanAccessDeniedException::limitReached(
                    $resourceType,
                    $limit,
                    PlanService::getCurrentPlan()?->nombre
                );
            }
        }
        
        return $next($request);
    }
}
