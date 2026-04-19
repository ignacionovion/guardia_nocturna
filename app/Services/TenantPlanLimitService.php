<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bed;
use App\Models\Guardia;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Support\Facades\Log;

/**
 * Service to enforce plan limits for tenants.
 * 
 * Validates if a tenant can create resources based on their plan limits.
 */
class TenantPlanLimitService
{
    private ?Plan $plan = null;
    private bool $planFetched = false;
    private ?string $planSource = null;

    /**
     * Check if tenant can create a new user
     */
    public function canCreateUser(): bool
    {
        $tenant = tenant();
        if (!$tenant) {
            Log::warning('tenant_blocked', [
                'reason' => 'no_tenant_context',
                'action' => 'create_user',
            ]);
            return false;
        }

        $limit = $this->getLimit('max_users');
        $currentCount = User::count();

        $allowed = $this->isWithinLimit($currentCount, $limit);

        if (!$allowed) {
            Log::warning('limit_exceeded', [
                'tenant_id' => $tenant->id,
                'resource' => 'max_users',
                'limit' => $limit,
                'current' => $currentCount,
            ]);
        }

        Log::info('PlanLimit: canCreateUser evaluated', [
            'tenant_id' => $tenant->id,
            'plan_slug' => $this->plan?->slug,
            'plan_source' => $this->planSource,
            'limit' => $limit,
            'current' => $currentCount,
            'allowed' => $allowed,
        ]);

        return $allowed;
    }

    /**
     * Check if tenant can create a new bed
     */
    public function canCreateBed(): bool
    {
        $tenant = tenant();
        if (!$tenant) {
            Log::warning('tenant_blocked', [
                'reason' => 'no_tenant_context',
                'action' => 'create_bed',
            ]);
            return false;
        }

        $limit = $this->getLimit('max_beds');
        $currentCount = Bed::count();

        $allowed = $this->isWithinLimit($currentCount, $limit);

        if (!$allowed) {
            Log::warning('limit_exceeded', [
                'tenant_id' => $tenant->id,
                'resource' => 'max_beds',
                'limit' => $limit,
                'current' => $currentCount,
            ]);
        }

        Log::info('PlanLimit: canCreateBed evaluated', [
            'tenant_id' => $tenant->id,
            'plan_slug' => $this->plan?->slug,
            'plan_source' => $this->planSource,
            'limit' => $limit,
            'current' => $currentCount,
            'allowed' => $allowed,
        ]);

        return $allowed;
    }

    /**
     * Check if tenant can create a new guardia
     */
    public function canCreateGuardia(): bool
    {
        $tenant = tenant();
        if (!$tenant) {
            Log::warning('tenant_blocked', [
                'reason' => 'no_tenant_context',
                'action' => 'create_guardia',
            ]);
            return false;
        }

        $limit = $this->getLimit('max_guardias');

        // Count guardias in current month
        $currentCount = Guardia::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $allowed = $this->isWithinLimit($currentCount, $limit);

        if (!$allowed) {
            Log::warning('limit_exceeded', [
                'tenant_id' => $tenant->id,
                'resource' => 'max_guardias',
                'limit' => $limit,
                'current' => $currentCount,
            ]);
        }

        Log::info('PlanLimit: canCreateGuardia evaluated', [
            'tenant_id' => $tenant->id,
            'plan_slug' => $this->plan?->slug,
            'plan_source' => $this->planSource,
            'limit' => $limit,
            'current' => $currentCount,
            'allowed' => $allowed,
        ]);

        return $allowed;
    }

    /**
     * Check if tenant can upload more storage
     */
    public function canUploadStorage(int $sizeInBytes): bool
    {
        $tenant = tenant();
        if (!$tenant) {
            Log::warning('tenant_blocked', [
                'reason' => 'no_tenant_context',
                'action' => 'upload_storage',
            ]);
            return false;
        }

        $limit = $this->getLimit('max_storage_mb');

        if ($limit === null) {
            Log::info('PlanLimit: canUploadStorage evaluated', [
                'tenant_id' => $tenant->id,
                'plan_slug' => $this->plan?->slug,
                'plan_source' => $this->planSource,
                'limit' => null,
                'allowed' => true,
            ]);
            return true;
        }

        if ($limit <= 0) {
            Log::warning('limit_exceeded', [
                'tenant_id' => $tenant->id,
                'resource' => 'max_storage_mb',
                'limit' => $limit,
                'current' => $this->getCurrentStorageUsageMb(),
                'attempt_upload_mb' => $sizeInBytes / 1024 / 1024,
            ]);

            Log::info('PlanLimit: canUploadStorage evaluated', [
                'tenant_id' => $tenant->id,
                'plan_slug' => $this->plan?->slug,
                'plan_source' => $this->planSource,
                'limit' => $limit,
                'allowed' => false,
            ]);
            return false;
        }

        $currentUsageMb = $this->getCurrentStorageUsageMb();
        $newSizeMb = $sizeInBytes / 1024 / 1024;
        $allowed = ($currentUsageMb + $newSizeMb) <= $limit;

        if (!$allowed) {
            Log::warning('limit_exceeded', [
                'tenant_id' => $tenant->id,
                'resource' => 'max_storage_mb',
                'limit' => $limit,
                'current' => $currentUsageMb,
                'attempt_upload_mb' => $newSizeMb,
            ]);
        }

        Log::info('PlanLimit: canUploadStorage evaluated', [
            'tenant_id' => $tenant->id,
            'plan_slug' => $this->plan?->slug,
            'plan_source' => $this->planSource,
            'limit' => $limit,
            'current_mb' => $currentUsageMb,
            'new_upload_mb' => $newSizeMb,
            'allowed' => $allowed,
        ]);

        return $allowed;
    }

    /**
     * Get current usage statistics
     */
    public function getCurrentUsage(): array
    {
        $tenant = tenant();
        if (!$tenant) {
            return [
                'plan_name' => 'N/A',
                'users' => ['current' => 0, 'limit' => 0, 'unlimited' => true],
                'beds' => ['current' => 0, 'limit' => 0, 'unlimited' => true],
                'guardias' => ['current' => 0, 'limit' => 0, 'unlimited' => true],
                'storage_mb' => ['current' => 0, 'limit' => 0, 'unlimited' => true],
            ];
        }

        $plan = $this->getPlan();

        return [
            'plan_name' => $plan->nombre,
            'users' => [
                'current' => User::count(),
                'limit' => $this->getLimit('max_users'),
                'unlimited' => $this->getLimit('max_users') === null,
            ],
            'beds' => [
                'current' => Bed::count(),
                'limit' => $this->getLimit('max_beds'),
                'unlimited' => $this->getLimit('max_beds') === null,
            ],
            'guardias' => [
                'current' => Guardia::whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count(),
                'limit' => $this->getLimit('max_guardias'),
                'unlimited' => $this->getLimit('max_guardias') === null,
            ],
            'storage_mb' => [
                'current' => $this->getCurrentStorageUsageMb(),
                'limit' => $this->getLimit('max_storage_mb'),
                'unlimited' => $this->getLimit('max_storage_mb') === null,
            ],
        ];
    }

    /**
     * Get remaining capacity for a resource
     */
    public function getRemainingCapacity(string $resource): ?int
    {
        $usage = $this->getCurrentUsage();
        
        if (!isset($usage[$resource])) {
            return null;
        }

        if ($usage[$resource]['unlimited']) {
            return null; // unlimited
        }

        return max(0, $usage[$resource]['limit'] - $usage[$resource]['current']);
    }

    /**
     * Get limit for a specific resource type
     */
    private function getPlan(): ?Plan
    {
        if ($this->planFetched) {
            if (!$this->plan) {
                Log::error('plan_missing', [
                    'tenant_id' => tenant()?->id,
                    'plan_source' => $this->planSource,
                ]);

                throw new \Exception('Tenant sin plan asignado. Sistema inconsistente.');
            }

            return $this->plan;
        }

        $tenant = tenant();
        if (!$tenant) {
            $this->planFetched = true;
            $this->planSource = 'no_tenant_context';
            Log::warning('tenant_blocked', [
                'reason' => 'no_tenant_context',
                'action' => 'resolve_plan',
            ]);
            return null;
        }

        $centralTenant = Tenant::query()
            ->select(['id', 'plan_id'])
            ->with('planRelation')
            ->find($tenant->id);

        if ($centralTenant) {
            $tenant = $centralTenant;
            $this->plan = $tenant->planRelation;
            $this->planSource = 'central_tenant_relation';
        } else {
            $this->plan = PlanService::planForTenant($tenant);
            $this->planSource = 'runtime_tenant_relation';
        }

        $this->planFetched = true;

        if (!$this->plan) {
            Log::error('plan_missing', [
                'tenant_id' => $tenant->id,
                'plan_id' => $tenant->plan_id,
                'plan_source' => $this->planSource,
            ]);

            throw new \Exception('Tenant sin plan asignado. Sistema inconsistente.');
        }

        Log::info('plan_resolved', [
            'tenant_id' => $tenant->id,
            'plan_id' => $tenant->plan_id,
            'resolved_plan_slug' => $this->plan->slug,
            'resolved_plan_id' => $this->plan->id,
            'plan_source' => $this->planSource,
        ]);

        return $this->plan;
    }

    protected function getLimit(string $type): ?int
    {
        $plan = $this->getPlan();

        if (!$plan) {
            throw new \Exception('Tenant sin plan asignado. Sistema inconsistente.');
        }

        return match($type) {
            'max_users' => $plan->max_users,
            'max_beds' => $plan->max_beds,
            'max_guardias' => $plan->max_guardias,
            'max_storage_mb' => $plan->max_storage_mb,
            default => null,
        };
    }

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

    /**
     * Get current storage usage in MB
     */
    protected function getCurrentStorageUsageMb(): float
    {
        $id = tenant()?->id;
        if ($id === null) {
            return 0.0;
        }

        return $this->directorySizeMb(storage_path('app/tenant-' . $id))
            + $this->directorySizeMb(storage_path('app/public/branding/' . $id))
            + $this->directorySizeMb(storage_path('app/tenants/' . $id));
    }

    private function directorySizeMb(string $path): float
    {
        if (!is_dir($path)) {
            return 0.0;
        }

        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size / 1024 / 1024;
    }

    /**
     * Get user-friendly error message for limit exceeded
     */
    public function getLimitExceededMessage(string $resource): string
    {
        $usage = $this->getCurrentUsage();
        
        if (!isset($usage[$resource])) {
            return 'Límite desconocido alcanzado.';
        }

        $current = $usage[$resource]['current'];
        $limit = $usage[$resource]['limit'];

        return match($resource) {
            'users' => "Has alcanzado el límite de usuarios de tu plan ({$current}/{$limit}). Actualiza tu plan para agregar más usuarios.",
            'beds' => "Has alcanzado el límite de camas de tu plan ({$current}/{$limit}). Actualiza tu plan para agregar más camas.",
            'guardias' => "Has alcanzado el límite de guardias mensuales de tu plan ({$current}/{$limit}). Actualiza tu plan para crear más guardias.",
            'storage_mb' => "Has alcanzado el límite de almacenamiento de tu plan ({$current}MB/{$limit}MB). Actualiza tu plan para más espacio.",
            default => 'Has alcanzado el límite de tu plan actual.',
        };
    }
}
