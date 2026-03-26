<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        \Log::info('=== RedirectIfAuthenticated Middleware (guest) ===', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'session_id' => $request->session()->getId(),
            'guards' => $guards,
            'cookies_received' => array_keys($request->cookies->all()),
        ]);

        foreach ($guards as $guard) {
            $isAuthenticated = Auth::guard($guard)->check();
            $userId = Auth::guard($guard)->user()?->id;
            
            \Log::info('RedirectIfAuthenticated: Checking guard', [
                'guard' => $guard ?? 'default',
                'is_authenticated' => $isAuthenticated,
                'user_id' => $userId,
            ]);

            if ($isAuthenticated) {
                // Central guard → central dashboard
                if ($guard === 'central') {
                    \Log::info('RedirectIfAuthenticated: Redirecting to /admin (central)');
                    return redirect('/admin');
                }
                
                // Web guard (tenant) → tenant dashboard
                \Log::info('RedirectIfAuthenticated: Redirecting to /dashboard (tenant)');
                return redirect('/dashboard');
            }
        }

        \Log::info('RedirectIfAuthenticated: Allowing access (not authenticated)');
        return $next($request);
    }
}
