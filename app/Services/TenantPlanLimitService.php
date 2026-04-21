<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bed;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Fachada de cuotas por plan: delega en {@see PlanService} (fuente única de verdad).
 * Mantiene firmas usadas por controladores y branding; la lógica numérica vive en PlanService.
 */
class TenantPlanLimitService
{
    public function canCreateUser(): bool
    {
        if (! tenant()) {
            Log::warning('tenant_blocked', ['reason' => 'no_tenant_context', 'action' => 'create_user']);

            return false;
        }

        $allowed = ! PlanService::exceedsLimit('users', 1);

        if (! $allowed) {
            Log::warning('limit_exceeded', [
                'tenant_id' => tenant()->id,
                'resource' => 'users',
                'limit' => PlanService::getLimit('users'),
                'current' => PlanService::getCurrentUsage('users'),
            ]);
        }

        return $allowed;
    }

    public function canCreateBed(): bool
    {
        if (! tenant()) {
            Log::warning('tenant_blocked', ['reason' => 'no_tenant_context', 'action' => 'create_bed']);

            return false;
        }

        $allowed = ! PlanService::exceedsLimit('beds', 1);

        if (! $allowed) {
            Log::warning('limit_exceeded', [
                'tenant_id' => tenant()->id,
                'resource' => 'beds',
                'limit' => PlanService::getLimit('beds'),
                'current' => PlanService::getCurrentUsage('beds'),
            ]);
        }

        return $allowed;
    }

    /**
     * Equipos de guardia ({@see Guardia}) vs. max_guardias del plan.
     */
    public function canCreateGuardia(): bool
    {
        if (! tenant()) {
            Log::warning('tenant_blocked', ['reason' => 'no_tenant_context', 'action' => 'create_guardia']);

            return false;
        }

        $allowed = ! PlanService::exceedsLimit('guardias', 1);

        if (! $allowed) {
            Log::warning('limit_exceeded', [
                'tenant_id' => tenant()->id,
                'resource' => 'guardias',
                'limit' => PlanService::getLimit('guardias'),
                'current' => PlanService::getCurrentUsage('guardias'),
            ]);
        }

        return $allowed;
    }

    public function canUploadStorage(int $sizeInBytes): bool
    {
        if (! tenant()) {
            Log::warning('tenant_blocked', ['reason' => 'no_tenant_context', 'action' => 'upload_storage']);

            return false;
        }

        $additionalMb = (int) ceil($sizeInBytes / 1024 / 1024);

        return ! PlanService::exceedsLimit('storage', $additionalMb);
    }

    public function canCreateVolunteer(): bool
    {
        if (! tenant()) {
            Log::warning('tenant_blocked', ['reason' => 'no_tenant_context', 'action' => 'create_volunteer']);

            return false;
        }

        $allowed = ! PlanService::exceedsLimit('volunteers', 1);

        if (! $allowed) {
            Log::warning('limit_exceeded', [
                'tenant_id' => tenant()->id,
                'resource' => 'volunteers',
                'limit' => PlanService::getLimit('volunteers'),
                'current' => PlanService::getCurrentUsage('volunteers'),
            ]);
        }

        return $allowed;
    }

    public function getCurrentUsage(): array
    {
        if (! tenant()) {
            return [
                'plan_name' => 'N/A',
                'users' => ['current' => 0, 'limit' => null, 'unlimited' => true],
                'volunteers' => ['current' => 0, 'limit' => null, 'unlimited' => true],
                'beds' => ['current' => 0, 'limit' => null, 'unlimited' => true],
                'guardias' => ['current' => 0, 'limit' => null, 'unlimited' => true],
                'storage_mb' => ['current' => 0, 'limit' => null, 'unlimited' => true],
            ];
        }

        $plan = PlanService::getCurrentPlan();

        return [
            'plan_name' => $plan?->nombre ?? 'Sin plan',
            'users' => [
                'current' => User::count(),
                'limit' => PlanService::getLimit('users'),
                'unlimited' => PlanService::getLimit('users') === null,
            ],
            'volunteers' => [
                'current' => Bombero::query()->count(),
                'limit' => PlanService::getLimit('volunteers'),
                'unlimited' => PlanService::getLimit('volunteers') === null,
            ],
            'beds' => [
                'current' => Bed::count(),
                'limit' => PlanService::getLimit('beds'),
                'unlimited' => PlanService::getLimit('beds') === null,
            ],
            'guardias' => [
                'current' => Guardia::query()->count(),
                'limit' => PlanService::getLimit('guardias'),
                'unlimited' => PlanService::getLimit('guardias') === null,
            ],
            'storage_mb' => [
                'current' => PlanService::getCurrentUsage('storage'),
                'limit' => PlanService::getLimit('storage'),
                'unlimited' => PlanService::getLimit('storage') === null,
            ],
        ];
    }

    public function getRemainingCapacity(string $resource): ?int
    {
        $usage = $this->getCurrentUsage();

        if (! isset($usage[$resource])) {
            return null;
        }

        if ($usage[$resource]['unlimited'] || $usage[$resource]['limit'] === null) {
            return null;
        }

        return max(0, (int) $usage[$resource]['limit'] - (int) $usage[$resource]['current']);
    }

    public function getLimitExceededMessage(string $resource): string
    {
        $usage = $this->getCurrentUsage();

        if (! isset($usage[$resource])) {
            return 'Has alcanzado un límite de tu plan. Actualizá tu plan para continuar.';
        }

        $current = (int) $usage[$resource]['current'];
        $limit = $usage[$resource]['limit'];

        if ($limit === null) {
            return 'Has alcanzado un límite de tu plan. Actualizá tu plan para continuar.';
        }

        return match ($resource) {
            'users' => "Has alcanzado el límite de usuarios de tu plan ({$current}/{$limit}). Actualizá tu plan para seguir agregando usuarios.",
            'volunteers' => "Has alcanzado el límite de voluntarios de tu plan ({$current}/{$limit}). Actualizá tu plan para seguir agregando voluntarios.",
            'beds' => "Has alcanzado el límite de camas de tu plan ({$current}/{$limit}). Actualizá tu plan para seguir agregando camas.",
            'guardias' => "Has alcanzado el límite de equipos de guardia de tu plan ({$current}/{$limit}). Actualizá tu plan para crear más guardias.",
            'storage_mb' => "Has alcanzado el límite de almacenamiento de tu plan ({$current} MB / {$limit} MB). Actualizá tu plan para más espacio.",
            default => 'Has alcanzado el límite de tu plan actual. Actualizá tu plan para continuar.',
        };
    }

    /**
     * @internal Tests unitarios de comparación de cupo.
     */
    private function isWithinLimit(int|float $current, ?int $limit): bool
    {
        if ($limit === null) {
            return true;
        }

        if ($limit <= 0) {
            return false;
        }

        return $current < $limit;
    }
}
