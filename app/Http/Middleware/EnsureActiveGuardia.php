<?php

namespace App\Http\Middleware;

use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveGuardia
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'guardia') {
            return $next($request);
        }

        if (!$user->guardia_id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')->withErrors(['access' => 'Tu cuenta de guardia no está asociada a ninguna guardia.']);
        }

        $guardia = Guardia::find($user->guardia_id);

        if (!$guardia || !$guardia->is_active_week) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')->withErrors(['access' => 'Tu guardia no está activa en este momento. Contacta al capitán.']);
        }

        return $next($request);
    }
}
