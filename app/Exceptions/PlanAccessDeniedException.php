<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Plan;
use App\Support\CentralUrls;
use Illuminate\Contracts\Debug\ShouldntReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlanAccessDeniedException extends \Exception implements ShouldntReport
{
    public const KIND_FEATURE = 'feature';

    public const KIND_INACTIVE = 'inactive';

    public const KIND_LIMIT = 'limit';

    public const KIND_NO_PLAN = 'no_plan';

    public const KIND_NO_TENANT = 'no_tenant';

    public function __construct(
        string $message,
        public readonly string $kind = self::KIND_FEATURE,
        public readonly ?string $blockedFeature = null,
        public readonly ?string $currentPlanName = null,
        public readonly ?string $requiredPlanName = null,
    ) {
        parent::__construct($message);
    }

    public static function organizationNotResolved(): self
    {
        return new self(
            'No pudimos identificar tu organización. Volvé a iniciar sesión e intentá de nuevo.',
            self::KIND_NO_TENANT,
        );
    }

    public static function noPlanAssigned(): self
    {
        return new self(
            'Tu cuenta no tiene un plan asignado. Elegí un plan para continuar.',
            self::KIND_NO_PLAN,
        );
    }

    public static function featureNotIncluded(string $feature, ?string $currentPlanName = null): self
    {
        $label = self::featureLabel($feature);

        return new self(
            "Tu plan actual no incluye el módulo «{$label}». Actualizá tu plan para habilitarlo.",
            self::KIND_FEATURE,
            $feature,
            $currentPlanName,
            null,
        );
    }

    public static function tenantInactive(?string $currentPlanName = null): self
    {
        return new self(
            'Tu cuenta está suspendida o inactiva. Actualizá tu plan para reactivarla.',
            self::KIND_INACTIVE,
            null,
            $currentPlanName,
        );
    }

    public static function limitReached(string $limitType, int $max, ?string $currentPlanName = null): self
    {
        $resource = self::limitLabel($limitType);

        return new self(
            "Has alcanzado el límite de {$resource} de tu plan (máx. {$max}). Actualizá tu plan para continuar.",
            self::KIND_LIMIT,
            $limitType,
            $currentPlanName,
        );
    }

    /**
     * Mensaje corto según el motivo del bloqueo (UX / conversión).
     */
    public static function denialKindMessage(?string $kind): string
    {
        return match ($kind) {
            self::KIND_FEATURE => 'Este módulo no está incluido en tu plan.',
            self::KIND_LIMIT => 'Has alcanzado el límite de tu plan.',
            self::KIND_INACTIVE => 'Tu cuenta está suspendida.',
            self::KIND_NO_PLAN => 'Necesitás un plan activo para continuar.',
            self::KIND_NO_TENANT => 'No pudimos cargar los datos de tu organización.',
            default => '',
        };
    }

    /**
     * Texto principal de conversión según el tipo de bloqueo.
     */
    public static function conversionSubtitle(?string $kind, ?string $blockedFeature): string
    {
        if ($kind === null || $kind === '') {
            return 'Compará los planes y cambiá el tuyo cuando lo necesites. El monto se ajusta según tu ciclo de facturación.';
        }

        if ($kind === self::KIND_INACTIVE) {
            return 'Elegí un plan para reactivar tu cuenta y seguir usando el sistema.';
        }

        if ($kind === self::KIND_NO_PLAN) {
            return 'Elegí un plan que se ajuste a tu organización y empezá en segundos.';
        }

        if ($kind === self::KIND_NO_TENANT) {
            return 'Si ya tenés un plan, podés gestionarlo desde esta pantalla.';
        }

        if ($kind === self::KIND_LIMIT && $blockedFeature !== null && $blockedFeature !== '') {
            $resource = self::limitLabel($blockedFeature);

            return "Para superar el límite de {$resource} necesitás actualizar tu plan.";
        }

        $label = self::featureLabel($blockedFeature);

        return "Para acceder a {$label} necesitás actualizar tu plan.";
    }

    public function render(Request $request): Response|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $this->getMessage(),
                'feature' => $this->blockedFeature,
                'upgrade_url' => CentralUrls::billingPlans(),
                'tenant_upgrade_url' => route('tenant.upgrade'),
                'kind' => $this->kind,
                'current_plan' => $this->currentPlanName,
                'required_plan' => $this->requiredPlanName,
            ], 422);
        }

        $request->session()->flash('blocked_feature', $this->blockedFeature);
        $request->session()->flash('plan_denial_kind', $this->kind);

        return redirect()
            ->route('tenant.upgrade')
            ->with('plan_denial', [
                'message' => $this->getMessage(),
                'kind' => $this->kind,
                'feature' => $this->blockedFeature,
                'feature_label' => self::resourceLabelForDenial($this->kind, $this->blockedFeature),
                'current_plan' => $this->currentPlanName,
                'required_plan' => $this->requiredPlanName,
            ]);
    }

    /**
     * Etiqueta humana del recurso bloqueado (módulo, addon o tipo de límite).
     */
    public static function resourceLabelForDenial(?string $kind, ?string $blocked): string
    {
        if ($blocked === null || $blocked === '') {
            return self::featureLabel(null);
        }

        if ($kind === self::KIND_LIMIT) {
            return self::limitLabel($blocked);
        }

        return self::featureLabel($blocked);
    }

    public static function featureLabel(?string $feature): string
    {
        if ($feature === null || $feature === '') {
            return 'esta función';
        }

        $modules = Plan::availableModules();
        $addons = Plan::availableAddons();

        return $modules[$feature]
            ?? $addons[$feature]
            ?? ucfirst(str_replace('_', ' ', $feature));
    }

    private static function limitLabel(string $type): string
    {
        return match ($type) {
            'users' => 'usuarios',
            'volunteers' => 'voluntarios',
            'guardias' => 'guardias',
            'beds' => 'camas',
            'storage' => 'almacenamiento',
            default => $type,
        };
    }

    /**
     * Plan a destacar como recomendado en la pantalla de upgrade.
     */
    public static function isPlanRecommended(Plan $plan, ?string $kind, ?string $blockedFeature, ?Plan $currentPlan): bool
    {
        if ($kind === self::KIND_INACTIVE || $kind === self::KIND_NO_TENANT) {
            return false;
        }

        if ($kind === self::KIND_FEATURE || $kind === self::KIND_NO_PLAN) {
            if ($blockedFeature === null || $blockedFeature === '') {
                return false;
            }

            return $plan->hasFeature($blockedFeature);
        }

        if ($kind === self::KIND_LIMIT && $blockedFeature !== null && $blockedFeature !== '') {
            return self::planHasBetterLimit($plan, $blockedFeature, $currentPlan);
        }

        return false;
    }

    private static function planHasBetterLimit(Plan $plan, string $limitType, ?Plan $currentPlan): bool
    {
        $candidate = $plan->getLimit($limitType);
        if ($candidate === null) {
            return true;
        }

        if ($currentPlan === null) {
            return true;
        }

        $current = $currentPlan->getLimit($limitType);
        if ($current === null) {
            return false;
        }

        return $candidate > $current;
    }
}
