<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\PlanAccessDeniedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('tenant.upgrade')) {
            return $next($request);
        }

        $tenant = tenant();

        if (!$tenant) {
            throw PlanAccessDeniedException::organizationNotResolved();
        }

        if ($tenant->activo !== true) {
            $tenant->loadMissing('planRelation');
            throw PlanAccessDeniedException::tenantInactive($tenant->planRelation?->nombre);
        }

        return $next($request);
    }
}
