<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bed;
use App\Models\Guardia;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service to enforce plan limits for tenants.
 * 
 * Validates if a tenant can create resources based on their plan limits.
 */
class TenantPlanLimitService
{
    private ?Plan $plan = null;
    private bool $planFetched = false;

    /**
     * Check if tenant can create a new user
     */
    public function canCreateUser(): bool
    {
        $tenant = tenant();
        if (!$tenant) {
            return false;
        }

        $limit = $this->getLimit('max_users');
        
        // null = unlimited
        if ($limit === null) {
            return true;
        }

        $currentCount = User::count();
        
        return $currentCount < $limit;
    }

    /**
     * Check if tenant can create a new bed
     */
    public function canCreateBed(): bool
    {
        $tenant = tenant();
        if (!$tenant) {
            return false;
        }

        $limit = $this->getLimit('max_beds');
        
        // null = unlimited
        if ($limit === null) {
            return true;
        }

        $currentCount = Bed::count();
        
        return $currentCount < $limit;
    }

    /**
     * Check if tenant can create a new guardia
     */
    public function canCreateGuardia(): bool
    {
        $tenant = tenant();
        if (!$tenant) {
            return false;
        }

        $limit = $this->getLimit('max_guardias');
        
        // null = unlimited
        if ($limit === null) {
            return true;
        }

        // Count guardias in current month
        $currentCount = Guardia::whereYear('fecha', now()->year)
            ->whereMonth('fecha', now()->month)
            ->count();
        
        return $currentCount < $limit;
    }

    /**
     * Check if tenant can upload more storage
     */
    public function canUploadStorage(int $sizeInBytes): bool
    {
        $tenant = tenant();
        if (!$tenant) {
            return false;
        }

        $limit = $this->getLimit('max_storage_mb');
        
        // null = unlimited
        if ($limit === null) {
            return true;
        }

        $currentUsageMb = $this->getCurrentStorageUsageMb();
        $newSizeMb = $sizeInBytes / 1024 / 1024;
        
        return ($currentUsageMb + $newSizeMb) <= $limit;
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
            'plan_name' => $plan?->nombre ?? 'Básico (por defecto)',
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
                'current' => Guardia::whereYear('fecha', now()->year)
                    ->whereMonth('fecha', now()->month)
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
            return $this->plan;
        }

        $tenant = tenant();
        if (!$tenant) {
            $this->planFetched = true;
            return null;
        }

        // Cargar la relación para asegurar que esté disponible
        $tenant->loadMissing('plan');
        $this->plan = $tenant->plan;

        $this->planFetched = true;
        return $this->plan;
    }

    protected function getLimit(string $type): ?int
    {
        $plan = $this->getPlan();
        $tenant = tenant(); // Asumimos que existe si llegamos aquí

        // If no plan found, use restrictive defaults (basic plan)
        if (!$plan) {
            Log::warning('Tenant has no plan assigned. Using default basic limits.', [
                'tenant_id' => $tenant->id,
            ]);

            return match($type) {
                'max_users' => 5,
                'max_beds' => 10,
                'max_guardias' => 20,
                'max_storage_mb' => 100,
                default => 0,
            };
        }

        return match($type) {
            'max_users' => $plan->max_users,
            'max_beds' => $plan->max_beds,
            'max_guardias' => $plan->max_guardias,
            'max_storage_mb' => $plan->max_storage_mb,
            default => null,
        };
    }

    /**
     * Get current storage usage in MB
     */
    protected function getCurrentStorageUsageMb(): float
    {
        // This is a placeholder - implement based on your storage strategy
        // Could track uploads in a table, or scan storage directory
        
        $storagePath = storage_path('app/tenant-' . tenant()->id);
        
        if (!is_dir($storagePath)) {
            return 0;
        }

        // Calculate directory size
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($storagePath)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size / 1024 / 1024; // Convert to MB
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
