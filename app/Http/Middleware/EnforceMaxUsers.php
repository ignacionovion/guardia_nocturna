<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\PlanAccessDeniedException;
use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea la creación de usuarios cuando el tenant alcanzó max_users del plan.
 * Lanza {@see PlanAccessDeniedException} (misma UX que otros bloqueos de plan → /upgrade).
 */
class EnforceMaxUsers
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! tenant()) {
            return $next($request);
        }

        $maxUsers = PlanService::getLimit('users');

        if ($maxUsers === null) {
            return $next($request);
        }

        if ($maxUsers <= 0) {
            throw PlanAccessDeniedException::limitReached('users', $maxUsers, PlanService::getCurrentPlan()?->nombre);
        }

        $currentUsers = \App\Models\User::count();

        if ($currentUsers >= (int) $maxUsers) {
            throw PlanAccessDeniedException::limitReached('users', (int) $maxUsers, PlanService::getCurrentPlan()?->nombre);
        }

        return $next($request);
    }
}
