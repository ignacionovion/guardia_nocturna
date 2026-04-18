<?php

declare(strict_types=1);

namespace App\Services;

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
    
    // Obtener límite para el tenant actual
    public static function getLimit(string $type): ?int
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return 0;
        }

        $plan = self::resolvePlanForTenant($tenant);
        if (!$plan) {
            Log::warning('PlanService: limit requested without assigned plan', [
                'tenant_id' => $tenant->id,
                'limit_type' => $type,
            ]);
            return 0;
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
    
    // Verificar si excede el límite
    public static function exceedsLimit(string $type, int $additional = 0): bool
    {
        $limit = self::getLimit($type);
        
        if ($limit === null) {
            return false; // Ilimitado
        }
        
        $current = self::getCurrentUsage($type);
        
        return ($current + $additional) > $limit;
    }
    
    // Obtener uso actual según el tipo (desde contexto central)
    public static function getCurrentUsageForTenant(Tenant $tenant, string $type): int
    {
        return $tenant->run(function () use ($type, $tenant) {
            return match($type) {
                'users' => \App\Models\User::count(),
                'guardias' => \App\Models\User::where('role', 'guardia')->count(),
                'beds' => \App\Models\Bed::count(),
                'storage' => self::calculateStorageUsageForTenant($tenant),
                default => 0,
            };
        });
    }
    
    // Calcular uso de storage en MB (desde contexto central)
    protected static function calculateStorageUsageForTenant(Tenant $tenant): int
    {
        $path = storage_path("app/tenants/{$tenant->id}");
        
        if (!is_dir($path)) {
            return 0;
        }
        
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return (int) ($size / 1024 / 1024); // Convertir a MB
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

        if (!$plan) {
            Log::warning('PlanService: tenant limit requested without assigned plan', [
                'tenant_id' => $tenant->id,
                'limit_type' => $type,
            ]);
            return 0;
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
        
        return $tenant->run(function () use ($type) {
            return match($type) {
                'users' => \App\Models\User::count(),
                'guardias' => \App\Models\User::where('role', 'guardia')->count(),
                'beds' => \App\Models\Bed::count(),
                'storage' => self::calculateStorageUsage(),
                default => 0,
            };
        });
    }
    
    // Calcular uso de storage en MB
    protected static function calculateStorageUsage(): int
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return 0;
        }
        
        $path = storage_path("app/tenants/{$tenant->id}");
        
        if (!is_dir($path)) {
            return 0;
        }
        
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return (int) ($size / 1024 / 1024); // Convertir a MB
    }
    
    // Obtener información completa de uso para UI (desde contexto central)
    public static function getUsageInfoForTenant(Tenant $tenant): array
    {
        $types = ['users', 'guardias', 'beds', 'storage'];
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
                'exceeded' => $limit !== null && $current > $limit,
            ];
        }
        
        return $info;
    }
    
    // Obtener información completa de uso para UI
    public static function getUsageInfo(): array
    {
        $types = ['users', 'guardias', 'beds', 'storage'];
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
                'exceeded' => $limit !== null && $current > $limit,
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
