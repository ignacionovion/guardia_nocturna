<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInventoryAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['super_admin', 'capitania', 'inventario'], true)) {
            abort(403, 'No autorizado.');
        }

        return $next($request);
    }
}
