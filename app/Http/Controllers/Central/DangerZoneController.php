<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAuditLog;
use App\Services\SaasDangerZoneService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

final class DangerZoneController extends Controller
{
    public function __construct(
        private readonly SaasDangerZoneService $dangerZone,
    ) {}

    public function show(Request $request)
    {
        abort_unless($request->user('central')?->is_super_admin === true, 403);

        $enabled = (bool) config('saas_danger_zone.enabled');
        $counts = $this->dangerZone->snapshotCounts();

        return view('central.danger-zone.index', [
            'enabled' => $enabled,
            'counts' => $counts,
            'phrase' => SaasDangerZoneService::WRITTEN_CONFIRMATION_PHRASE,
        ]);
    }

    public function clearTenants(Request $request): RedirectResponse
    {
        abort_unless($request->user('central')?->is_super_admin === true, 403);
        $this->assertDangerZoneEnabled();

        $validated = $request->validate([
            'written_confirmation' => [
                'required',
                'string',
                Rule::in([SaasDangerZoneService::WRITTEN_CONFIRMATION_PHRASE]),
            ],
            'confirm_irreversible' => ['accepted'],
            'confirm_environment' => ['accepted'],
        ], [
            'written_confirmation.in' => 'La frase de confirmación no coincide exactamente.',
            'confirm_irreversible.accepted' => 'Debes marcar la casilla de irreversibilidad.',
            'confirm_environment.accepted' => 'Debes confirmar que estás en un entorno adecuado (p. ej. staging).',
        ]);

        $countsBefore = $this->dangerZone->snapshotCounts();

        CentralAuditLog::log(
            'danger_zone_clear_tenants_started',
            'Inicio: vaciar compañías (tenants, dominios, facturación, pagos, cuerpos y datos centrales ligados).',
            null,
            [
                'mode' => 'clear_tenants',
                'counts_before' => $countsBefore,
                'written_confirmation_length' => strlen($validated['written_confirmation']),
            ]
        );

        try {
            $result = $this->dangerZone->clearTenantsAndOrgData();
        } catch (\Throwable $e) {
            Log::error('[danger-zone] clear_tenants falló', ['exception' => $e]);

            return redirect()
                ->route('central.danger-zone.show')
                ->with('error', 'La operación falló: ' . $e->getMessage());
        }

        CentralAuditLog::log(
            'danger_zone_clear_tenants_completed',
            'Completado: vaciar compañías.',
            null,
            [
                'mode' => 'clear_tenants',
                'result' => $result,
                'counts_before' => $countsBefore,
            ]
        );

        return redirect()
            ->route('central.danger-zone.show')
            ->with('success', 'Vaciado de compañías completado. Revisa auditoría y entorno.');
    }

    public function resetSaas(Request $request): RedirectResponse
    {
        abort_unless($request->user('central')?->is_super_admin === true, 403);
        $this->assertDangerZoneEnabled();

        $validated = $request->validate([
            'written_confirmation' => [
                'required',
                'string',
                Rule::in([SaasDangerZoneService::WRITTEN_CONFIRMATION_PHRASE]),
            ],
            'confirm_irreversible' => ['accepted'],
            'confirm_environment' => ['accepted'],
            'confirm_audit_wipe' => ['accepted'],
        ], [
            'written_confirmation.in' => 'La frase de confirmación no coincide exactamente.',
            'confirm_irreversible.accepted' => 'Debes marcar la casilla de irreversibilidad.',
            'confirm_environment.accepted' => 'Debes confirmar que estás en un entorno adecuado (p. ej. staging).',
            'confirm_audit_wipe.accepted' => 'Debes aceptar explícitamente el borrado del historial de auditoría central.',
        ]);

        $countsBefore = $this->dangerZone->snapshotCounts();

        // No registrar "inicio" en BD: el reset vacía `central_audit_logs` y se perdería.
        Log::critical('[danger-zone] reset_saas solicitado (pre-ejecución)', [
            'counts_before' => $countsBefore,
            'admin_id' => $request->user('central')?->id,
        ]);

        try {
            $result = $this->dangerZone->resetSaasOperationalState();
        } catch (\Throwable $e) {
            Log::error('[danger-zone] reset_saas falló', ['exception' => $e]);

            return redirect()
                ->route('central.danger-zone.show')
                ->with('error', 'La operación falló: ' . $e->getMessage());
        }

        CentralAuditLog::log(
            'danger_zone_reset_saas_completed',
            'Completado: reset total SaaS.',
            null,
            [
                'mode' => 'reset_saas',
                'result' => $result,
                'counts_before' => $countsBefore,
            ]
        );

        return redirect()
            ->route('central.danger-zone.show')
            ->with('success', 'Reset total SaaS completado. Auditoría previa fue eliminada salvo este evento final; revisa también storage/logs si necesitas trazabilidad previa.');
    }

    private function assertDangerZoneEnabled(): void
    {
        abort_unless((bool) config('saas_danger_zone.enabled'), 403, 'La zona de peligro está deshabilitada. Define SAAS_DANGER_ZONE_ENABLED=true en .env (o usa APP_ENV=local).');
    }
}
