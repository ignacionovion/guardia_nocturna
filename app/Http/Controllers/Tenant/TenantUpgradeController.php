<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Exceptions\PlanAccessDeniedException;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TenantUpgradeController extends Controller
{
    public function __invoke(): View
    {
        $plans = Plan::query()->active()->ordered()->get();
        $denial = session('plan_denial');
        $blockedFeature = session('blocked_feature') ?? $denial['feature'] ?? null;
        $denialKind = session('plan_denial_kind') ?? $denial['kind'] ?? null;
        $currentPlan = tenant()?->planRelation;

        $recommendedPlanId = null;
        if ($blockedFeature && $denialKind && $plans->isNotEmpty()) {
            foreach ($plans as $candidate) {
                if (PlanAccessDeniedException::isPlanRecommended($candidate, $denialKind, $blockedFeature, $currentPlan)) {
                    $recommendedPlanId = (int) $candidate->getKey();
                    break;
                }
            }
        }

        return view('tenant.upgrade', [
            'plans' => $plans,
            'denial' => $denial,
            'recommendedPlanId' => $recommendedPlanId,
        ]);
    }

    public function upgrade(Request $request, string $planId): RedirectResponse
    {
        Log::info('TenantUpgradeController.upgrade: entry', [
            'route_plan_id' => $planId,
        ]);

        $plan = Plan::query()->findOrFail((int) $planId);

        Log::info('TenantUpgradeController.upgrade: plan resolved', [
            'route_plan_id' => $planId,
            'resolved_plan_id' => $plan->getKey(),
            'plan_slug' => $plan->slug,
        ]);

        $tenant = tenant();

        Log::info('TenantUpgradeController.upgrade: tenant resolved', [
            'tenant_id' => $tenant?->getKey(),
            'resolved_plan_id' => $plan->getKey(),
            'plan_slug' => $plan->slug,
        ]);

        if (! $tenant) {
            Log::warning('TenantUpgradeController.upgrade: early return — no tenant', [
                'route_plan_id' => $planId,
                'resolved_plan_id' => $plan->getKey(),
            ]);

            return redirect()
                ->route('tenant.upgrade')
                ->with('error', 'No se pudo identificar tu organización.');
        }

        if (! $plan->activo) {
            Log::warning('TenantUpgradeController.upgrade: early return — plan not active', [
                'tenant_id' => $tenant->getKey(),
                'route_plan_id' => $planId,
                'resolved_plan_id' => $plan->getKey(),
                'plan_slug' => $plan->slug,
            ]);

            return back()->with('error', 'Este plan no está disponible.');
        }

        $billing = $tenant->billing;

        Log::info('TenantUpgradeController.upgrade: billing resolved', [
            'tenant_id' => $tenant->getKey(),
            'route_plan_id' => $planId,
            'resolved_plan_id' => $plan->getKey(),
            'plan_slug' => $plan->slug,
            'billing_id' => $billing?->getKey(),
            'billing_plan_id' => $billing?->plan_id,
            'billing_plan_slug' => $billing?->plan,
            'billing_cycle' => $billing?->billing_cycle,
            'billing_monto' => $billing?->monto,
        ]);

        if (! $billing) {
            Log::warning('TenantUpgradeController.upgrade: early return — no billing', [
                'tenant_id' => $tenant->getKey(),
                'route_plan_id' => $planId,
                'resolved_plan_id' => $plan->getKey(),
            ]);

            return redirect()
                ->route('tenant.upgrade')
                ->with('error', 'No pudimos aplicar el cambio desde aquí. Completá la suscripción o volvé a intentar en unos minutos.');
        }

        $billing->loadMissing('planRelation');

        Log::info('TenantUpgradeController.upgrade: before same-plan check', [
            'tenant_id' => $tenant->getKey(),
            'route_plan_id' => $planId,
            'target_plan_id' => $plan->getKey(),
            'billing_plan_id' => $billing->plan_id,
            'same_plan' => (int) $billing->plan_id === (int) $plan->getKey(),
        ]);

        if ((int) $billing->plan_id === (int) $plan->getKey()) {
            Log::warning('TenantUpgradeController.upgrade: early return — already on this plan (applyPlan skipped)', [
                'tenant_id' => $tenant->getKey(),
                'billing_plan_id' => $billing->plan_id,
                'target_plan_id' => $plan->getKey(),
                'billing_cycle' => $billing->billing_cycle,
                'billing_monto' => $billing->monto,
            ]);

            $this->forgetPlanDenialSession($request);

            return redirect()
                ->route('dashboard')
                ->with('success', 'Ya estás en este plan.');
        }

        $currentPlan = $billing->planRelation;

        Log::info('TenantUpgradeController.upgrade: before downgrade check', [
            'tenant_id' => $tenant->getKey(),
            'target_plan_id' => $plan->getKey(),
            'target_precio_mensual' => $plan->precio_mensual,
            'current_plan_id' => $currentPlan?->getKey(),
            'current_precio_mensual' => $currentPlan?->precio_mensual,
            'billing_plan_id' => $billing->plan_id,
            'billing_cycle' => $billing->billing_cycle,
            'billing_monto' => $billing->monto,
        ]);

        // Posible foco de revisión: solo se comparan precio_mensual del plan objetivo vs actual;
        // si el tenant factura en ciclo anual u otros matices, la decisión podría no alinear con expectativas.
        if (
            $currentPlan !== null
            && (float) $plan->precio_mensual < (float) $currentPlan->precio_mensual
        ) {
            Log::warning('TenantUpgradeController.upgrade: early return — downgrade not allowed', [
                'tenant_id' => $tenant->getKey(),
                'target_plan_id' => $plan->getKey(),
                'target_precio_mensual' => $plan->precio_mensual,
                'current_plan_id' => $currentPlan->getKey(),
                'current_precio_mensual' => $currentPlan->precio_mensual,
            ]);

            return back()->with('error', 'No se permite cambiar a un plan inferior.');
        }

        Log::info('TenantUpgradeController.upgrade: before applyPlan', [
            'tenant_id' => $tenant->getKey(),
            'target_plan_id' => $plan->getKey(),
            'plan_slug' => $plan->slug,
            'billing_plan_id_before' => $billing->plan_id,
            'billing_cycle' => $billing->billing_cycle,
            'billing_monto_before' => $billing->monto,
        ]);

        $billing->applyPlan($plan);

        Log::info('TenantUpgradeController.upgrade: after applyPlan', [
            'tenant_id' => $tenant->getKey(),
            'target_plan_id' => $plan->getKey(),
            'plan_slug' => $plan->slug,
            'billing_plan_id_after' => $billing->plan_id,
            'billing_plan_slug_after' => $billing->plan,
            'billing_cycle' => $billing->billing_cycle,
            'billing_monto_after' => $billing->monto,
        ]);

        $this->forgetPlanDenialSession($request);

        Log::info('TenantUpgradeController.upgrade: before final redirect to dashboard', [
            'tenant_id' => $tenant->getKey(),
            'billing_plan_id' => $billing->plan_id,
            'billing_monto' => $billing->monto,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Plan actualizado correctamente.');
    }

    private function forgetPlanDenialSession(Request $request): void
    {
        $request->session()->forget([
            'plan_denial',
            'blocked_feature',
            'plan_denial_kind',
        ]);
    }
}
