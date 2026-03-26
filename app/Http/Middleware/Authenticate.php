<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        \Log::info('🔴 === Authenticate Middleware ===', [
            'path' => $request->path(),
            'session_id' => $request->session()->getId(),
            'auth_check' => Auth::guard('web')->check(),
            'auth_user_id' => Auth::guard('web')->user()?->id,
            'auth_user_role' => Auth::guard('web')->user()?->role,
            'tenant_id' => tenant('id'),
        ]);

        if (!$request->expectsJson()) {
            $host = $request->getHost();
            $centralDomains = config('tenancy.central_domains', []);

            if (in_array($host, $centralDomains, true)) {
                \Log::info('🔴 Authenticate: REDIRECTING to /login (central)');
                return '/login'; // central
            }

            \Log::info('🔴 Authenticate: REDIRECTING to / (tenant)');
            return '/'; // tenant
        }

        \Log::info('🔴 Authenticate: ALLOWING (expects JSON)');
        return null;
    }
}
