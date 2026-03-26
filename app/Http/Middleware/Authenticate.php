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
        \Log::info('=== Authenticate Middleware ===', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'session_id' => $request->session()->getId(),
            'auth_check' => Auth::guard('web')->check(),
            'auth_user_id' => Auth::guard('web')->user()?->id,
            'cookies_received' => array_keys($request->cookies->all()),
            'will_redirect' => !$request->expectsJson() && !Auth::guard('web')->check(),
        ]);

        if (!$request->expectsJson()) {
            $host = $request->getHost();
            $centralDomains = config('tenancy.central_domains', []);

            if (in_array($host, $centralDomains, true)) {
                \Log::info('Authenticate: Redirecting to /login (central)');
                return '/login'; // central
            }

            \Log::info('Authenticate: Redirecting to / (tenant)');
            return '/'; // tenant
        }

        return null;
    }
}
