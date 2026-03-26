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

        \Log::info('🟡 === EnsureActiveGuardia Middleware ===', [
            'path' => $request->path(),
            'session_id' => $request->session()->getId(),
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'guardia_id' => $user?->guardia_id,
            'tenant_id' => tenant('id'),
        ]);

        if (!$user || $user->role !== 'guardia') {
            \Log::info('🟡 EnsureActiveGuardia: ALLOWING (not guardia role)');
            return $next($request);
        }

        if (!$user->guardia_id) {
            \Log::warning('🟡 EnsureActiveGuardia: REDIRECTING to / (no guardia_id) + LOGOUT');
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')->withErrors(['access' => 'Tu cuenta de guardia no está asociada a ninguna guardia.']);
        }

        $guardia = Guardia::find($user->guardia_id);

        if (!$guardia || !$guardia->is_active_week) {
            \Log::warning('🟡 EnsureActiveGuardia: REDIRECTING to / (guardia not active) + LOGOUT', [
                'guardia_exists' => $guardia ? 'yes' : 'no',
                'is_active_week' => $guardia?->is_active_week,
            ]);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')->withErrors(['access' => 'Tu guardia no está activa en este momento. Contacta al capitán.']);
        }

        \Log::info('🟡 EnsureActiveGuardia: ALLOWING (guardia active)');
        return $next($request);
    }
}
