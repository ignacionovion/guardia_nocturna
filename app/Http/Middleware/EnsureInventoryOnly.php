<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInventoryOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // PRE-TENANCY diagnostic: runs in web group BEFORE InitializeTenancyBySubdomain
        // This log always goes to laravel.log - confirms request reached Laravel
        if (str_contains($request->path(), 'regenerate-credentials')) {
            $debugLog = base_path('storage/logs/debug-regenerate.log');
            $ts = date('[Y-m-d H:i:s]');
            file_put_contents($debugLog, "$ts [WEB_MW] EnsureInventoryOnly hit - method={$request->method()} path={$request->path()} host={$request->getHost()}\n", FILE_APPEND);
        }

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
