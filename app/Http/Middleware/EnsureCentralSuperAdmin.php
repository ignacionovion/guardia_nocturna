<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Solo administradores SaaS con flag is_super_admin (gestión de central_admins).
 */
final class EnsureCentralSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('central')->user();

        if ($user === null || ! $user->is_super_admin) {
            abort(403, 'No autorizado para administrar usuarios del panel central.');
        }

        return $next($request);
    }
}
