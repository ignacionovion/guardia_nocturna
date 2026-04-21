<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\PlanAccessDeniedException;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;

class PlanService
{
    /**
     * Plan efectivo del tenant, siempre alineado con `plan_id` (evita relación Eloquent stale tras upgrade/cambio).
     */
    public static function planForTenant(Tenant $tenant): ?Plan
    {
        return self::resolvePlanForTenant($tenant);
    }

    private static function resolvePlanForTenant(Tenant $tenant): ?Plan
    {
        if (! $tenant->plan_id) {
            return null;
        }

        if ($tenant->relationLoaded('planRelation') && $tenant->planRelation) {
            if ((int) $tenant->planRelation->getKey() !== (int) $tenant->plan_id) {
                $tenant->unsetRelation('planRelation');
            } else {
                return $tenant->planRelation;
            }
        }

        $tenant->loadMissing('planRelation');

        return $tenant->planRelation;
    }

    private static function getCurrentTenantPlan(): ?Plan
    {
        $tenant = tenant();

        if (!$tenant) {
            return null;
        }

        return self::resolvePlanForTenant($tenant);
    }

    // Verificar si el tenant actual tiene un addon
    public static function hasAddon(string $addon): bool
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return false;
        }

        $plan = self::resolvePlanForTenant($tenant);
        if (!$plan) {
            Log::warning('PlanService: addon check without assigned plan', [
                'tenant_id' => $tenant->id,
                'addon' => $addon,
            ]);
            return false;
        }

        return $plan->hasAddon($addon);
    }
    
    // Verificar si el tenant actual tiene una feature
    public static function hasFeature(string $feature): bool
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return false;
        }

        $plan = self::resolvePlanForTenant($tenant);
        if (!$plan) {
            Log::warning('PlanService: feature check without assigned plan', [
                'tenant_id' => $tenant->id,
                'feature' => $feature,
            ]);
            return false;
        }

        return $plan->hasFeature($feature);
    }
    
    /**
     * Límite numérico del plan actual (null = ilimitado). Sin plan asignado → null (no forzar "0" como cuota).
     */
    public static function getLimit(string $type): ?int
    {
        $tenant = tenant();

        if (! $tenant) {
            return null;
        }

        $plan = self::resolvePlanForTenant($tenant);
        if (! $plan) {
            Log::warning('PlanService: limit requested without assigned plan', [
                'tenant_id' => $tenant->id,
                'limit_type' => $type,
            ]);

            return null;
        }

        return $plan->getLimit($type);
    }
    
    // Verificar si está cerca del límite (80% o más)
    public static function isNearLimit(string $type): bool
    {
        $limit = self::getLimit($type);
        
        if ($limit === null) {
            return false; // Ilimitado
        }
        
        $current = self::getCurrentUsage($type);
        $threshold = (int) ($limit * 0.8);
        
        return $current >= $threshold;
    }
    
    /**
     * Indica si, tras sumar $additional unidades de uso, se supera el cupo del plan.
     * Cupo ≤ 0 se interpreta como sin capacidad (siempre excede).
     */
    public static function exceedsLimit(string $type, int $additional = 0): bool
    {
        $limit = self::getLimit($type);

        if ($limit === null) {
            return false;
        }

        if ($limit <= 0) {
            return true;
        }

        $current = self::getCurrentUsage($type);

        return ($current + $additional) > $limit;
    }

    /**
     * Bloqueo centralizado para altas (usuarios, camas, equipos de guardia, etc.).
     *
     * @throws PlanAccessDeniedException
     */
    public static function assertCanIncrement(string $limitType, int $delta = 1): void
    {
        if ($delta < 1) {
            return;
        }

        $tenant = tenant();
        if (! $tenant) {
            return;
        }

        $plan = self::getCurrentPlan();
        if (! $plan) {
            throw PlanAccessDeniedException::noPlanAssigned();
        }

        if (self::exceedsLimit($limitType, $delta)) {
            $max = $plan->getLimit($limitType) ?? 0;

            throw PlanAccessDeniedException::limitReached($limitType, (int) $max, $plan->nombre);
        }
    }

    /**
     * @throws PlanAccessDeniedException
     */
    public static function assertCanUploadBytes(int $sizeBytes): void
    {
        if ($sizeBytes <= 0) {
            return;
        }

        $tenant = tenant();
        if (! $tenant) {
            return;
        }

        $plan = self::getCurrentPlan();
        if (! $plan) {
            throw PlanAccessDeniedException::noPlanAssigned();
        }

        $limitMb = $plan->getLimit('storage');
        if ($limitMb === null) {
            return;
        }

        if ($limitMb <= 0) {
            throw PlanAccessDeniedException::limitReached('storage', (int) $limitMb, $plan->nombre);
        }

        $additionalMb = (int) ceil($sizeBytes / 1024 / 1024);
        if (self::exceedsLimit('storage', $additionalMb)) {
            throw PlanAccessDeniedException::limitReached('storage', (int) $limitMb, $plan->nombre);
        }
    }

    /**
     * Texto "uso / límite" para UI (ej. "8 / 10" o "220 / 500 MB").
     */
    public static function usageLabel(string $type): string
    {
        $current = self::getCurrentUsage($type);
        $limit = self::getLimit($type);

        if ($limit === null) {
            return $type === 'storage'
                ? sprintf('%d MB / ilimitado', $current)
                : sprintf('%d / ilimitado', $current);
        }

        return $type === 'storage'
            ? sprintf('%d / %d MB', $current, $limit)
            : sprintf('%d / %d', $current, $limit);
    }
    
    // Obtener uso actual según el tipo (desde contexto central)
    public static function getCurrentUsageForTenant(Tenant $tenant, string $type): int
    {
        return $tenant->run(function () use ($type, $tenant) {
            return match ($type) {
                'users' => \App\Models\User::count(),
                'volunteers' => Bombero::query()->count(),
                'guardias' => Guardia::query()->count(),
                'beds' => \App\Models\Bed::count(),
                'storage' => self::storageUsageMbForTenantId((string) $tenant->id),
                default => 0,
            };
        });
    }

    /**
     * @return list<string> Rutas de disco consideradas para cupo de almacenamiento del tenant.
     */
    public static function tenantStorageRootPaths(?string $tenantId = null): array
    {
        $id = $tenantId ?? tenant()?->id;
        if ($id === null || $id === '') {
            return [];
        }

        return array_values(array_filter([
            storage_path('app/tenant-' . $id),
            storage_path('app/public/branding/' . $id),
            storage_path('app/tenants/' . $id),
        ]));
    }

    public static function storageUsageMbForTenantId(string $tenantId): int
    {
        $bytes = 0;
        foreach (self::tenantStorageRootPaths($tenantId) as $path) {
            $bytes += self::directorySizeBytes($path);
        }

        return (int) floor($bytes / 1024 / 1024);
    }

    private static function directorySizeBytes(string $path): int
    {
        if (! is_dir($path)) {
            return 0;
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

        return $size;
    }
    
    // Verificar si está cerca del límite (desde contexto central)
    protected static function isNearLimitForTenant(Tenant $tenant, string $type): bool
    {
        $limit = self::getLimitForTenant($tenant, $type);
        
        if ($limit === null) {
            return false; // Ilimitado
        }
        
        $current = self::getCurrentUsageForTenant($tenant, $type);
        $threshold = (int) ($limit * 0.8);
        
        return $current >= $threshold;
    }
    
    // Obtener límite según tenant y tipo
    protected static function getLimitForTenant(Tenant $tenant, string $type): ?int
    {
        $plan = self::resolvePlanForTenant($tenant);

        if (! $plan) {
            Log::warning('PlanService: tenant limit requested without assigned plan', [
                'tenant_id' => $tenant->id,
                'limit_type' => $type,
            ]);

            return null;
        }

        return $plan->getLimit($type);
    }
    
    // Obtener uso actual según el tipo
    public static function getCurrentUsage(string $type): int
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return 0;
        }
        
        return $tenant->run(function () use ($type, $tenant) {
            return match ($type) {
                'users' => \App\Models\User::count(),
                'volunteers' => Bombero::query()->count(),
                'guardias' => Guardia::query()->count(),
                'beds' => \App\Models\Bed::count(),
                'storage' => self::storageUsageMbForTenantId((string) $tenant->id),
                default => 0,
            };
        });
    }
    
    // Obtener información completa de uso para UI (desde contexto central)
    public static function getUsageInfoForTenant(Tenant $tenant): array
    {
        $types = ['users', 'volunteers', 'guardias', 'beds', 'storage'];
        $info = [];
        
        foreach ($types as $type) {
            $limit = self::getLimitForTenant($tenant, $type);
            $current = self::getCurrentUsageForTenant($tenant, $type);
            
            $info[$type] = [
                'limit' => $limit,
                'current' => $current,
                'remaining' => $limit !== null ? max(0, $limit - $current) : null,
                'percentage' => $limit !== null ? round(($current / $limit) * 100, 1) : null,
                'unlimited' => $limit === null,
                'near_limit' => self::isNearLimitForTenant($tenant, $type),
                'exceeded' => $limit !== null && $current >= $limit,
            ];
        }

        return $info;
    }

    // Obtener información completa de uso para UI
    public static function getUsageInfo(): array
    {
        $types = ['users', 'volunteers', 'guardias', 'beds', 'storage'];
        $info = [];
        
        foreach ($types as $type) {
            $limit = self::getLimit($type);
            $current = self::getCurrentUsage($type);
            
            $info[$type] = [
                'limit' => $limit,
                'current' => $current,
                'remaining' => $limit !== null ? max(0, $limit - $current) : null,
                'percentage' => $limit !== null ? round(($current / $limit) * 100, 1) : null,
                'unlimited' => $limit === null,
                'near_limit' => self::isNearLimit($type),
                'exceeded' => $limit !== null && $current >= $limit,
            ];
        }

        return $info;
    }

    // Cambiar plan de un tenant (usado desde panel central)
    public static function changePlan(Tenant $tenant, int $planId): bool
    {
        $plan = Plan::find($planId);
        
        if (!$plan || !$plan->activo) {
            return false;
        }
        
        $tenant->update([
            'plan_id' => $plan->id,
        ]);
        
        return true;
    }
    
    // Obtener plan actual del tenant
    public static function getCurrentPlan(): ?Plan
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return null;
        }

        return self::resolvePlanForTenant($tenant);
    }
}
