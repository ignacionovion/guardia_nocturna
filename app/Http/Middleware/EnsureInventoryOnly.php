<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInventoryOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'inventario') {
            return $next($request);
        }

        $path = ltrim((string) $request->path(), '/');

        if ($path === '' || $path === '/') {
            return redirect()->route('inventario.index');
        }

        if ($path === 'logout') {
            return $next($request);
        }

        if (str_starts_with($path, 'inventario')) {
            return $next($request);
        }

        return redirect()->route('inventario.index');
    }
}
