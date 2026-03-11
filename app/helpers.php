<?php

use App\Services\FeatureFlagService;
use App\Services\PlanService;

if (!function_exists('feature')) {
    /**
     * Check if a feature is enabled for the current tenant.
     * First checks plan-based features, then falls back to feature flags.
     *
     * Usage in views: @if(feature('inventario')) ... @endif
     * Usage in code:  if (feature('reportes_avanzados')) { ... }
     */
    function feature(string $feature, mixed $default = false): mixed
    {
        // First check plan-based features (new system)
        if (PlanService::hasFeature($feature)) {
            return true;
        }
        
        // Fallback to legacy feature flags
        $service = app(FeatureFlagService::class);
        $value = $service->get($feature);

        return $value ?? $default;
    }
}

if (!function_exists('plan_limit')) {
    /**
     * Get the limit for a specific resource type.
     * Returns null if unlimited.
     *
     * Usage: plan_limit('users') // returns max users allowed
     */
    function plan_limit(string $type): ?int
    {
        return PlanService::getLimit($type);
    }
}

if (!function_exists('plan_usage')) {
    /**
     * Get current usage for a specific resource type.
     *
     * Usage: plan_usage('users') // returns current user count
     */
    function plan_usage(string $type): int
    {
        return PlanService::getCurrentUsage($type);
    }
}

if (!function_exists('plan_remaining')) {
    /**
     * Get remaining quota for a specific resource type.
     * Returns null if unlimited.
     *
     * Usage: plan_remaining('users') // returns remaining users allowed
     */
    function plan_remaining(string $type): ?int
    {
        $limit = PlanService::getLimit($type);
        
        if ($limit === null) {
            return null;
        }
        
        $current = PlanService::getCurrentUsage($type);
        
        return max(0, $limit - $current);
    }
}

if (!function_exists('plan_exceeded')) {
    /**
     * Check if the tenant has exceeded the limit for a resource type.
     *
     * Usage: if (plan_exceeded('users')) { ... }
     */
    function plan_exceeded(string $type): bool
    {
        return PlanService::exceedsLimit($type);
    }
}

