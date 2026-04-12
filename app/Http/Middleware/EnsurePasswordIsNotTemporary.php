<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsNotTemporary
{
    private const ALLOWED_ROUTE_NAMES = [
        'password.initial',
        'password.initial.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if ($user === null) {
            return $next($request);
        }

        if (!$user->password_must_change) {
            return $next($request);
        }

        $route = $request->route();
        $name = $route?->getName();

        if ($name !== null && in_array($name, self::ALLOWED_ROUTE_NAMES, true)) {
            return $next($request);
        }

        if ($request->is('password/initial')) {
            return $next($request);
        }

        return redirect()->route('password.initial');
    }
}
