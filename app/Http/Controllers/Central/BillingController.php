<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /**
     * Display billing dashboard
     */
    public function index()
    {
        $stats = [
            'pagos_pendientes' => Billing::pendientes()->count(),
            'por_vencer' => Billing::porVencer(7)->count(),
            'vencidos' => Billing::vencidos()->count(),
            'ingresos_estimados' => Billing::whereIn('estado_pago', ['pagado', 'pendiente', 'trial'])->sum('monto'),
        ];

        $billings = Billing::with('tenant')
            ->orderBy('fecha_vencimiento', 'asc')
            ->paginate(20);

        $planes = ['basico', 'profesional', 'enterprise'];

        return view('central.billing.index', compact('stats', 'billings', 'planes'));
    }

    /**
     * Mark billing as paid
     */
    public function markPaid(Billing $billing)
    {
        $billing->marcarPagado();

        // Reactivar tenant si estaba suspendido
        if ($billing->tenant && !$billing->tenant->activo) {
            $billing->tenant->update(['activo' => true]);
        }

        return redirect()
            ->route('central.billing.index')
            ->with('success', 'Pago marcado como completado. Vencimiento extendido 1 mes.');
    }

    /**
     * Suspend tenant due to debt
     */
    public function suspend(Billing $billing)
    {
        $billing->suspender();

        return redirect()
            ->route('central.billing.index')
            ->with('success', 'Tenant suspendido por falta de pago.');
    }

    /**
     * Extend due date
     */
    public function extend(Request $request, Billing $billing)
    {
        $validated = $request->validate([
            'dias' => ['required', 'integer', 'min' => 1, 'max' => 365],
        ]);

        $billing->extenderVencimiento($validated['dias']);

        return redirect()
            ->route('central.billing.index')
            ->with('success', "Vencimiento extendido {$validated['dias']} días.");
    }

    /**
     * Change plan
     */
    public function changePlan(Request $request, Billing $billing)
    {
        $validated = $request->validate([
            'plan' => ['required', 'string', 'in:basico,profesional,enterprise,trial'],
        ]);

        // Get plan price automatically
        $plan = Plan::where('slug', $validated['plan'])->first();
        $nuevoMonto = $plan?->precio_mensual ?? 0;
        $montoAnterior = $billing->monto;

        $billing->update([
            'plan' => $validated['plan'],
            'monto' => $nuevoMonto,
        ]);

        // Update tenant plan as well
        $billing->tenant->update([
            'plan' => $validated['plan'],
            'plan_id' => $plan?->id,
        ]);

        return redirect()
            ->route('central.billing.index')
            ->with('success', "Plan cambiado a {$validated['plan']}. Monto actualizado: \${$montoAnterior} → \${$nuevoMonto}");
    }

    /**
     * Update observation
     */
    public function updateObservation(Request $request, Billing $billing)
    {
        $validated = $request->validate([
            'observacion' => ['nullable', 'string', 'max' => 500],
        ]);

        $billing->update(['observacion' => $validated['observacion']]);

        return redirect()
            ->route('central.billing.index')
            ->with('success', 'Observación actualizada.');
    }

    /**
     * Create billing record for tenant
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'plan' => ['required', 'string', 'in:basico,profesional,enterprise'],
            'monto' => ['required', 'numeric', 'min' => 0],
            'fecha_vencimiento' => ['required', 'date'],
        ]);

        Billing::create([
            'tenant_id' => $validated['tenant_id'],
            'plan' => $validated['plan'],
            'monto' => $validated['monto'],
            'estado_pago' => 'pendiente',
            'fecha_vencimiento' => $validated['fecha_vencimiento'],
        ]);

        return redirect()
            ->route('central.billing.index')
            ->with('success', 'Registro de facturación creado.');
    }
}
