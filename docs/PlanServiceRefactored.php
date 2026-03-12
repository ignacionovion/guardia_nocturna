<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantPlanOverride;
use Illuminate\Support\Facades\Cache;

/**
 * PlanService - Gestión de planes y features para SaaS multi-tenant
 * 
 * Responsabilidades:
 * - Verificar acceso a módulos del sistema
 * - Verificar add-ons comerciales
 * - Gestionar límites de uso
 * - Aplicar overrides personalizados por tenant
 * - Cachear configuraciones para performance
 */
class PlanService
{
    private const CACHE_TTL = 300; // 5 minutos

    // ==================== VERIFICACIÓN DE ACCESO ====================

    /**
     * Verificar si el tenant tiene acceso a un módulo del sistema
     */
    public static function hasModule(string $module): bool
    {
        return self::getFeatureValue('module', $module, false);
    }

    /**
     * Verificar si el tenant tiene un add-on habilitado
     */
    public static function hasAddon(string $addon): bool
    {
        return self::getFeatureValue('addon', $addon, false);
    }

    /**
     * Alias legacy para compatibilidad con código existente
     * @deprecated Usar hasModule() para módulos o hasAddon() para add-ons
     */
    public static function hasFeature(string $feature): bool
    {
        // Determinar si es módulo o addon basado en available lists
        if (array_key_exists($feature, Plan::availableModules())) {
            return self::hasModule($feature);
        }

        if (array_key_exists($feature, Plan::availableAddons())) {
            return self::hasAddon($feature);
        }

        // Fallback: buscar en ambos
        return self::hasModule($feature) || self::hasAddon($feature);
    }

    // ==================== LÍMITES ====================

    /**
     * Obtener límite para un recurso específico
     */
    public static function getLimit(string $type): ?int
    {
        $tenant = tenant();

        if (!$tenant) {
            return null;
        }

        $cacheKey = "tenant:{$tenant->id}:limit:{$type}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenant, $type) {
            // 1. Verificar override personalizado
            $override = TenantPlanOverride::where('tenant_id', $tenant->id)
                ->where('override_type', 'limit')
                ->where('override_key', $type)
                ->where(function ($q) {
                    $q->whereNull('valid_until')
                        ->orWhere('valid_until', '>', now());
                })
                ->first();

            if ($override) {
                return $override->override_value['value'] ?? null;
            }

            // 2. Obtener del plan asignado
            if ($tenant->plan_id) {
                $plan = Plan::find($tenant->plan_id);
                if ($plan) {
                    return $plan->getLimit($type);
                }
            }

            // 3. Legacy fallback
            return self::getLegacyLimit($tenant->getRawOriginal('plan') ?? 'basico', $type);
        });
    }

    /**
     * Verificar si hay límite (null = ilimitado)
     */
    public static function isUnlimited(string $type): bool
    {
        return self::getLimit($type) === null;
    }

    /**
     * Verificar si se ha excedido un límite
     */
    public static function limitExceeded(string $type, int $currentCount): bool
    {
        $limit = self::getLimit($type);

        if ($limit === null) {
            return false; // Ilimitado
        }

        return $currentCount >= $limit;
    }

    /**
     * Obtener uso actual vs límite
     */
    public static function getUsageRatio(string $type, int $currentCount): ?float
    {
        $limit = self::getLimit($type);

        if ($limit === null || $limit === 0) {
            return null;
        }

        return min(1.0, $currentCount / $limit);
    }

    // ==================== OVERRIDES POR TENANT ====================

    /**
     * Aplicar override temporal a un tenant
     */
    public static function applyOverride(
        string $tenantId,
        string $type, // 'module', 'addon', 'limit'
        string $key,
        mixed $value,
        ?\DateTime $validUntil = null,
        ?int $createdBy = null
    ): TenantPlanOverride {
        // Limpiar cache
        Cache::forget("tenant:{$tenantId}:feature:{$type}:{$key}");
        if ($type === 'limit') {
            Cache::forget("tenant:{$tenantId}:limit:{$key}");
        }

        return TenantPlanOverride::create([
            'tenant_id' => $tenantId,
            'override_type' => $type,
            'override_key' => $key,
            'override_value' => ['value' => $value, 'metadata' => []],
            'valid_until' => $validUntil,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Limpiar overrides expirados
     */
    public static function cleanupExpiredOverrides(): int
    {
        return TenantPlanOverride::where('valid_until', '<', now())
            ->whereNotNull('valid_until')
            ->delete();
    }

    // ==================== MÉTODOS PRIVADOS ====================

    /**
     * Obtener valor de feature con cache y overrides
     */
    private static function getFeatureValue(string $type, string $key, bool $default): bool
    {
        $tenant = tenant();

        if (!$tenant) {
            return $default;
        }

        $cacheKey = "tenant:{$tenant->id}:feature:{$type}:{$key}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenant, $type, $key, $default) {
            // 1. Verificar override personalizado
            $override = TenantPlanOverride::where('tenant_id', $tenant->id)
                ->where('override_type', $type)
                ->where('override_key', $key)
                ->where(function ($q) {
                    $q->whereNull('valid_until')
                        ->orWhere('valid_until', '>', now());
                })
                ->first();

            if ($override) {
                return (bool) ($override->override_value['value'] ?? $default);
            }

            // 2. Obtener del plan asignado
            if ($tenant->plan_id) {
                $plan = Plan::find($tenant->plan_id);
                if ($plan) {
                    if ($type === 'module') {
                        return $plan->hasModule($key);
                    }
                    if ($type === 'addon') {
                        return $plan->hasAddon($key);
                    }
                }
            }

            // 3. Legacy fallback (mantiene compatibilidad)
            return self::getLegacyFeature($tenant->getRawOriginal('plan') ?? 'basico', $key);
        });
    }

    /**
     * Mapeo legacy para compatibilidad con tenants antiguos
     */
    private static function getLegacyFeature(string $planSlug, string $feature): bool
    {
        $legacyMapping = [
            'basico' => [
                'voluntarios', 'emergencias', 'dotaciones', 'calendario',
                'guardia', 'camas'
            ],
            'profesional' => [
                'voluntarios', 'emergencias', 'dotaciones', 'calendario',
                'guardia', 'camas', 'reportes', 'planilla'
            ],
            'enterprise' => [
                'voluntarios', 'emergencias', 'dotaciones', 'calendario',
                'guardia', 'camas', 'reportes', 'planilla',
                'now', 'preventiva', 'inventario'
            ],
        ];

        $planFeatures = $legacyMapping[$planSlug] ?? $legacyMapping['basico'];
        return in_array($feature, $planFeatures, true);
    }

    private static function getLegacyLimit(string $planSlug, string $type): ?int
    {
        $limits = [
            'basico' => [
                'max_users' => 20,
                'max_guardias' => 3,
                'max_beds' => 10,
            ],
            'profesional' => [
                'max_users' => 50,
                'max_guardias' => 10,
                'max_beds' => 30,
            ],
            'enterprise' => [
                'max_users' => null,
                'max_guardias' => null,
                'max_beds' => null,
            ],
        ];

        return $limits[$planSlug][$type] ?? null;
    }

    // ==================== CACHE MANAGEMENT ====================

    /**
     * Limpiar todo el cache de un tenant
     */
    public static function clearTenantCache(string $tenantId): void
    {
        // Módulos
        foreach (array_keys(Plan::availableModules()) as $module) {
            Cache::forget("tenant:{$tenantId}:feature:module:{$module}");
        }

        // Addons
        foreach (array_keys(Plan::availableAddons()) as $addon) {
            Cache::forget("tenant:{$tenantId}:feature:addon:{$addon}");
        }

        // Límites
        foreach (array_keys(Plan::availableLimits()) as $limit) {
            Cache::forget("tenant:{$tenantId}:limit:{$limit}");
        }
    }

    /**
     * Limpiar cache cuando cambia el plan de un tenant
     */
    public static function onPlanChanged(Tenant $tenant): void
    {
        self::clearTenantCache($tenant->id);
    }
}
