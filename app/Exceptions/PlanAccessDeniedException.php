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
            'No pudimos identificar tu organización. Vuelve a iniciar sesión o contacta a soporte.',
            self::KIND_NO_TENANT,
        );
    }

    public static function noPlanAssigned(): self
    {
        return new self(
            'Tu cuenta no tiene un plan asignado. Elige un plan para continuar.',
            self::KIND_NO_PLAN,
        );
    }

    public static function featureNotIncluded(string $feature, ?string $currentPlanName = null): self
    {
        return new self(
            'Tu plan no incluye esta funcionalidad.',
            self::KIND_FEATURE,
            $feature,
            $currentPlanName,
            null,
        );
    }

    public static function tenantInactive(?string $currentPlanName = null): self
    {
        return new self(
            'Tu cuenta está suspendida o inactiva. Renueva tu plan o contacta a soporte.',
            self::KIND_INACTIVE,
            null,
            $currentPlanName,
        );
    }

    public static function limitReached(string $limitType, int $max, ?string $currentPlanName = null): self
    {
        $resource = self::limitLabel($limitType);

        return new self(
            "Has alcanzado el límite de {$resource} de tu plan ({$max}).",
            self::KIND_LIMIT,
            $limitType,
            $currentPlanName,
        );
    }

    public function render(Request $request): Response|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => $this->getMessage(),
                'feature' => $this->blockedFeature,
                'upgrade_url' => CentralUrls::billingPlans(),
                'kind' => $this->kind,
                'current_plan' => $this->currentPlanName,
                'required_plan' => $this->requiredPlanName,
            ], 422);
        }

        return redirect()
            ->route('tenant.upgrade')
            ->with('plan_denial', [
                'message' => $this->getMessage(),
                'kind' => $this->kind,
                'feature' => $this->blockedFeature,
                'feature_label' => self::featureLabel($this->blockedFeature),
                'current_plan' => $this->currentPlanName,
                'required_plan' => $this->requiredPlanName,
            ]);
    }

    public static function featureLabel(?string $feature): string
    {
        if ($feature === null || $feature === '') {
            return 'Esta función';
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
            'guardias' => 'guardias',
            'beds' => 'camas',
            'storage' => 'almacenamiento',
            default => $type,
        };
    }
}
