<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\CentralPaymentBillingService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
    public function markPaid(Request $request, Billing $billing, CentralPaymentBillingService $paymentBillingService)
    {
        $validated = $request->validate([
            'fecha_pago' => ['required', 'date'],
            'metodo_pago' => ['required', 'string', 'max:64'],
        ]);

        try {
            DB::connection('central')->transaction(function () use ($billing, $validated, $paymentBillingService): void {
                $payment = Payment::query()->create([
                    'tenant_id' => $billing->tenant_id,
                    'billing_id' => $billing->id,
                    'amount' => $billing->monto,
                    'currency' => 'CLP',
                    'payment_method' => $validated['metodo_pago'],
                    'status' => Payment::STATUS_PAID,
                    'reference' => null,
                    'notes' => 'Pago registrado manualmente desde facturación central',
                    'paid_at' => $validated['fecha_pago'],
                    'created_by_central_admin_id' => Auth::guard('central')->id(),
                ]);

                $paymentBillingService->syncTenantBillingFromPaidPayment($payment);
            });
        } catch (ValidationException $e) {
            return redirect()
                ->route('central.billing.index')
                ->withErrors($e->errors());
        }

        $billing->refresh();
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
        $montoAnterior = $billing->monto;

        $billing->applyPlan($plan);
        $billing->refresh();

        $nuevoMonto = $billing->monto;

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
            'fecha_vencimiento' => ['nullable', 'date'],
        ]);

        $plan = Plan::findOrFail((int) $validated['plan_id']);
        $cycle = $validated['billing_cycle'] ?? 'monthly';
        $monto = $validated['monto'] ?? $plan->montoSegunCiclo($cycle);

        $days = $cycle === 'yearly' ? 365 : 30;
        $fechaVencimiento = ! empty($validated['fecha_vencimiento'])
            ? Carbon::parse($validated['fecha_vencimiento'])->startOfDay()
            : now()->addDays($days)->startOfDay();

        $billing = Billing::create([
            'tenant_id' => $validated['tenant_id'],
            'plan_id' => $plan->id,
            'plan' => $plan->slug,
            'billing_cycle' => $cycle,
            'monto' => $monto,
            'estado_pago' => 'pendiente',
            'fecha_vencimiento' => $fechaVencimiento,
        ]);
        $billing->syncToTenant();

        return redirect()
            ->route('central.billing.index')
            ->with('success', 'Registro de facturación creado.');
    }
}
