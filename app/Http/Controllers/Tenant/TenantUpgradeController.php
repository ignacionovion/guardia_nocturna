<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Exceptions\PlanAccessDeniedException;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantUpgradeController extends Controller
{
    public function __invoke(): View
    {
        $plans = Plan::query()->active()->ordered()->get();
        $denial = session('plan_denial');
        $blockedFeature = session('blocked_feature') ?? $denial['feature'] ?? null;
        $denialKind = session('plan_denial_kind') ?? $denial['kind'] ?? null;
        $t = tenant();
        $currentPlan = $t ? PlanService::planForTenant($t) : null;

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

    public function upgrade(Request $request, string $tenant, string $targetPlan): RedirectResponse
    {
        // Domain routes (`{tenant}.…`) + path: both parameters must appear in the signature for correct injection.
        $plan = Plan::query()->findOrFail((int) $targetPlan);

        $currentTenant = tenant();

        if (! $currentTenant) {
            return redirect()
                ->route('tenant.upgrade')
                ->with('error', 'No se pudo identificar tu organización.');
        }

        if (! $plan->activo) {
            return back()->with('error', 'Este plan no está disponible.');
        }

        $billing = $currentTenant->billing;

        if (! $billing) {
            return redirect()
                ->route('tenant.upgrade')
                ->with('error', 'No pudimos aplicar el cambio desde aquí. Completá la suscripción o volvé a intentar en unos minutos.');
        }

        $billing->loadMissing('planRelation');

        if ((int) $billing->plan_id === (int) $plan->getKey()) {
            $this->forgetPlanDenialSession($request);

            return redirect()
                ->route('dashboard')
                ->with('success', 'Ya estás en este plan.');
        }

        $currentPlan = $billing->planRelation;

        if (
            $currentPlan !== null
            && (float) $plan->precio_mensual < (float) $currentPlan->precio_mensual
        ) {
            return back()->with('error', 'No se permite cambiar a un plan inferior.');
        }

        $billing->applyPlan($plan);

        $syncedTenant = tenant();
        if ($syncedTenant !== null) {
            $syncedTenant->refresh();
            $syncedTenant->unsetRelation('planRelation');
        }

        $this->forgetPlanDenialSession($request);

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
