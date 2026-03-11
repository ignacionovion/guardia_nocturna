<?php

declare(strict_types=1);

namespace App\Http\Middleware;

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
                $current = PlanService::getCurrentUsage($resourceType);
                
                $message = match($resourceType) {
                    'users' => "Has alcanzado el límite de {$limit} usuarios. Actualiza tu plan para agregar más.",
                    'guardias' => "Has alcanzado el límite de {$limit} guardias. Actualiza tu plan para agregar más.",
                    'beds' => "Has alcanzado el límite de {$limit} camas. Actualiza tu plan para agregar más.",
                    default => "Has alcanzado el límite de tu plan. Actualiza para continuar.",
                };
                
                // If it's an AJAX request, return JSON
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Plan limit exceeded',
                        'message' => $message,
                        'limit' => $limit,
                        'current' => $current,
                    ], 403);
                }
                
                // Otherwise redirect back with error
                return redirect()->back()
                    ->with('error', $message)
                    ->with('plan_limit_exceeded', true);
            }
        }
        
        return $next($request);
    }
}
