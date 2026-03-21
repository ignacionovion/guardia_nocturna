<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCaptain
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['capitan', 'super_admin', 'capitania'], true)) {
            abort(403, 'Solo los capitanes pueden acceder a esta sección.');
        }

        return $next($request);
    }
}
