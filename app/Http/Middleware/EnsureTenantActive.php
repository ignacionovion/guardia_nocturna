<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to tenant app when the tenant is inactive or expired.
 * Shows a friendly "suspended" page instead of the normal app.
 */
class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (!$tenant) {
            return $next($request);
        }

        // Check if tenant is active
        if (!$tenant->activo) {
            return $this->suspendedResponse('inactiva');
        }

        // Check expiration date
        if ($tenant->fecha_vencimiento && $tenant->fecha_vencimiento->isPast()) {
            return $this->suspendedResponse('vencida');
        }

        return $next($request);
    }

    protected function suspendedResponse(string $reason): Response
    {
        $messages = [
            'inactiva' => [
                'title' => 'Cuenta Suspendida',
                'message' => 'Esta compañía ha sido desactivada por el administrador de la plataforma.',
            ],
            'vencida' => [
                'title' => 'Cuenta Vencida',
                'message' => 'El plan de esta compañía ha expirado. Contacte al administrador para renovar.',
            ],
        ];

        $data = $messages[$reason] ?? $messages['inactiva'];

        return response()->view('suspended', $data, 403);
    }
}
