<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to tenant app based on lifecycle state.
 *
 * Allowed: trial, activo, vencido (within grace period)
 * Blocked: suspendido, vencido (past grace), cancelado
 */
class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (!$tenant) {
            return $next($request);
        }

        $estado = $tenant->estado ?? 'activo';

        // Operational states — always allow
        if (in_array($estado, [Tenant::ESTADO_TRIAL, Tenant::ESTADO_ACTIVO])) {
            // Legacy check: also verify activo boolean
            if ($tenant->activo === false) {
                return $this->suspendedResponse('suspendido');
            }
            return $next($request);
        }

        // Vencido — allow during grace period
        if ($estado === Tenant::ESTADO_VENCIDO) {
            if ($tenant->isInGracePeriod()) {
                // Allow but could show a banner warning
                return $next($request);
            }
            return $this->suspendedResponse('vencido', $tenant);
        }

        // Suspendido
        if ($estado === Tenant::ESTADO_SUSPENDIDO) {
            return $this->suspendedResponse('suspendido');
        }

        // Cancelado
        if ($estado === Tenant::ESTADO_CANCELADO) {
            return $this->suspendedResponse('cancelado');
        }

        // Fallback: check legacy activo field
        if (!$tenant->activo) {
            return $this->suspendedResponse('suspendido');
        }

        // Check expiration date (legacy)
        if ($tenant->fecha_vencimiento && $tenant->fecha_vencimiento->isPast()) {
            return $this->suspendedResponse('vencido', $tenant);
        }

        return $next($request);
    }

    protected function suspendedResponse(string $reason, ?Tenant $tenant = null): Response
    {
        $messages = [
            'suspendido' => [
                'title' => 'Cuenta Suspendida',
                'message' => 'Esta compañía ha sido desactivada por el administrador de la plataforma.',
                'icon' => 'pause',
            ],
            'vencido' => [
                'title' => 'Cuenta Vencida',
                'message' => 'El plan de esta compañía ha expirado. Contacte al administrador para renovar.',
                'icon' => 'clock',
            ],
            'cancelado' => [
                'title' => 'Cuenta Cancelada',
                'message' => 'Esta cuenta ha sido cancelada permanentemente.',
                'icon' => 'x-circle',
            ],
        ];

        $data = $messages[$reason] ?? $messages['suspendido'];

        if ($tenant && $tenant->fecha_vencimiento) {
            $data['expired_at'] = $tenant->fecha_vencimiento->format('d/m/Y');
        }

        return response()->view('suspended', $data, 403);
    }
}
