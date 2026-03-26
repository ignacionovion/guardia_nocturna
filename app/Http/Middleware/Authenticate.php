<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        if (!$request->expectsJson()) {
            $host = $request->getHost();
            $centralDomains = config('tenancy.central_domains', []);

            if (in_array($host, $centralDomains, true)) {
                return '/login'; // central
            }

            return '/'; // tenant
        }

        return null;
    }
}
