<?php

declare(strict_types=1);

use App\Services\PlanService;
use App\Services\FeatureFlagService;

// ==================== HELPERS DE PLANES Y FEATURES ====================

if (!function_exists('feature')) {
    /**
     * Verificar si un feature está habilitado (legacy alias)
     * 
     * @deprecated Usar module() para módulos o addon() para add-ons
     */
    function feature(string $feature): bool
    {
        // Primero verificar feature flags (para pruebas A/B)
        if (FeatureFlagService::isEnabled($feature)) {
            return true;
        }

        // Luego verificar planes
        return PlanService::hasFeature($feature);
    }
}

if (!function_exists('module')) {
    /**
     * Verificar si un módulo del sistema está habilitado
     * 
     * Ejemplos:
     *   if (module('inventario')) { ... }
     *   if (module('reportes')) { ... }
     */
    function module(string $module): bool
    {
        return PlanService::hasModule($module);
    }
}

if (!function_exists('addon')) {
    /**
     * Verificar si un add-on comercial está habilitado
     * 
     * Ejemplos:
     *   if (addon('api_access')) { ... }
     *   if (addon('custom_branding')) { ... }
     */
    function addon(string $addon): bool
    {
        return PlanService::hasAddon($addon);
    }
}

if (!function_exists('plan_limit')) {
    /**
     * Obtener el límite de un recurso para el tenant actual
     * 
     * @return int|null El límite o null si es ilimitado
     */
    function plan_limit(string $type): ?int
    {
        return PlanService::getLimit($type);
    }
}

if (!function_exists('plan_usage')) {
    /**
     * Calcular uso actual vs límite (como porcentaje)
     * 
     * @return float|null Porcentaje entre 0 y 1, o null si es ilimitado
     */
    function plan_usage(string $type, int $currentCount): ?float
    {
        return PlanService::getUsageRatio($type, $currentCount);
    }
}

if (!function_exists('plan_remaining')) {
    /**
     * Obtener cuántos recursos quedan disponibles
     * 
     * @return int|null Cantidad restante o null si es ilimitado
     */
    function plan_remaining(string $type, int $currentCount): ?int
    {
        $limit = PlanService::getLimit($type);

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $currentCount);
    }
}

if (!function_exists('plan_exceeded')) {
    /**
     * Verificar si se ha excedido el límite de un recurso
     * 
     * Ejemplo:
     *   if (plan_exceeded('max_users', $userCount)) {
     *       return redirect()->back()->with('error', 'Límite alcanzado');
     *   }
     */
    function plan_exceeded(string $type, int $currentCount): bool
    {
        return PlanService::limitExceeded($type, $currentCount);
    }
}

if (!function_exists('plan_unlimited')) {
    /**
     * Verificar si un recurso es ilimitado en el plan actual
     */
    function plan_unlimited(string $type): bool
    {
        return PlanService::isUnlimited($type);
    }
}

// ==================== HELPERS DE UI ====================

if (!function_exists('plan_badge')) {
    /**
     * Generar badge HTML con el nombre del plan del tenant
     */
    function plan_badge(): string
    {
        $tenant = tenant();
        if (!$tenant?->plan) {
            return '';
        }

        $colors = [
            'basico' => 'bg-slate-100 text-slate-800',
            'profesional' => 'bg-blue-100 text-blue-800',
            'enterprise' => 'bg-purple-100 text-purple-800',
        ];

        $plan = strtolower($tenant->plan);
        $color = $colors[$plan] ?? 'bg-gray-100 text-gray-800';

        return sprintf(
            '<span class="px-2 py-1 text-xs font-medium rounded-full %s">%s</span>',
            $color,
            ucfirst($plan)
        );
    }
}

if (!function_exists('feature_list')) {
    /**
     * Obtener lista de features disponibles para mostrar en UI
     */
    function feature_list(): array
    {
        $tenant = tenant();
        if (!$tenant) {
            return [];
        }

        return [
            'modules' => \App\Models\Plan::availableModules(),
            'addons' => \App\Models\Plan::availableAddons(),
            'enabled_modules' => array_filter(\App\Models\Plan::availableModules(), function ($key) {
                return module($key);
            }, ARRAY_FILTER_USE_KEY),
            'enabled_addons' => array_filter(\App\Models\Plan::availableAddons(), function ($key) {
                return addon($key);
            }, ARRAY_FILTER_USE_KEY),
        ];
    }
}
