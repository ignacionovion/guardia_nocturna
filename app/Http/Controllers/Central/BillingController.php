<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Payment;
use App\Models\Plan;
use Illuminate\Http\Request;

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
            'trials' => Billing::trial()->count(),
            'ingresos_estimados' => Billing::whereIn('estado_pago', ['pagado', 'pendiente', 'trial'])->sum('monto'),
        ];

        $billings = Billing::with(['tenant.planRelation', 'planRelation'])
            ->orderBy('fecha_vencimiento', 'asc')
            ->paginate(20);

        $planes = Plan::active()->ordered()->get(['id', 'slug', 'nombre', 'precio_mensual']);

        return view('central.billing.index', compact('stats', 'billings', 'planes'));
    }

    /**
     * Mark billing as paid and create payment record
     */
    public function markPaid(Request $request, Billing $billing)
    {
        $validated = $request->validate([
            'fecha_pago' => ['required', 'date'],
            'metodo_pago' => ['nullable', 'string', 'max:50'],
        ]);

        // Crear registro en payments
        Payment::create([
            'tenant_id' => $billing->tenant_id,
            'monto' => $billing->monto,
            'fecha_pago' => $validated['fecha_pago'],
            'metodo_pago' => $validated['metodo_pago'],
            'observacion' => 'Pago registrado manualmente desde panel admin',
        ]);

        // Actualizar billing con fecha de pago y recalcular vencimiento (incluye sync a tenants)
        $billing->marcarPagado($validated['fecha_pago']);

        $ciclo = $billing->billing_cycle === 'yearly' ? '1 año' : '1 mes';

        return redirect()
            ->route('central.billing.index')
            ->with('success', "Pago registrado. Vencimiento extendido {$ciclo}.");
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
            'dias' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $billing->extenderVencimiento((int) $validated['dias']);

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
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $plan = Plan::findOrFail((int) $validated['plan_id']);
        $nuevoMonto = $plan->montoSegunCiclo($billing->billing_cycle ?? 'monthly');
        $montoAnterior = $billing->monto;

        $billing->update([
            'plan_id' => $plan->id,
            'plan' => $plan->slug,
            'monto' => $nuevoMonto,
        ]);
        $billing->syncToTenant();

        return redirect()
            ->route('central.billing.index')
            ->with('success', "Plan cambiado a {$plan->slug}. Monto actualizado: \${$montoAnterior} → \${$nuevoMonto}");
    }

    /**
     * Update observation
     */
    public function updateObservation(Request $request, Billing $billing)
    {
        $validated = $request->validate([
            'observacion' => ['nullable', 'string', 'max:500'],
        ]);

        $billing->update(['observacion' => $validated['observacion']]);

        return redirect()
            ->route('central.billing.index')
            ->with('success', 'Observación actualizada.');
    }

    /**
     * Change billing cycle (monthly/yearly)
     */
    public function changeBillingCycle(Request $request, Billing $billing)
    {
        $validated = $request->validate([
            'billing_cycle' => ['required', 'in:monthly,yearly'],
        ]);

        $billing->loadMissing('planRelation');
        $plan = $billing->planRelation ?? Plan::find($billing->plan_id);
        if (!$plan) {
            return redirect()
                ->route('central.billing.index')
                ->with('error', 'No se encontró el plan asociado a esta facturación.');
        }

        $newCycle = $validated['billing_cycle'];
        $billing->update([
            'billing_cycle' => $newCycle,
            'monto' => $plan->montoSegunCiclo($newCycle),
        ]);
        $billing->syncToTenant();

        $cicloLabel = $newCycle === 'yearly' ? 'Anual' : 'Mensual';

        return redirect()
            ->route('central.billing.index')
            ->with('success', "Ciclo de facturación cambiado a {$cicloLabel}. Monto actualizado según plan.");
    }

    /**
     * Create billing record for tenant
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string', 'exists:tenants,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'monto' => ['nullable', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'fecha_vencimiento' => ['required', 'date'],
        ]);

        $plan = Plan::findOrFail((int) $validated['plan_id']);
        $cycle = $validated['billing_cycle'] ?? 'monthly';
        $monto = $validated['monto'] ?? $plan->montoSegunCiclo($cycle);

        $billing = Billing::create([
            'tenant_id' => $validated['tenant_id'],
            'plan_id' => $plan->id,
            'plan' => $plan->slug,
            'billing_cycle' => $cycle,
            'monto' => $monto,
            'estado_pago' => 'pendiente',
            'fecha_vencimiento' => $validated['fecha_vencimiento'],
        ]);
        $billing->syncToTenant();

        return redirect()
            ->route('central.billing.index')
            ->with('success', 'Registro de facturación creado.');
    }
}
