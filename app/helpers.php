<?php

use App\Services\BrandingService;
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

if (!function_exists('branding')) {
    /**
     * Get branding data for the current tenant.
     * Returns an object with branding settings or defaults.
     *
     * Usage in views: {{ branding()->logo }}
     * Usage in code:  $branding = branding();
     */
    function branding(): object
    {
        $service = app(BrandingService::class);
        return $service->getBranding();
    }
}

if (!function_exists('addon')) {
    /**
     * Check if an addon is enabled for the current tenant.
     * Addons are commercial features beyond the core modules.
     *
     * Usage in views: @if(addon('custom_branding')) ... @endif
     * Usage in code:  if (addon('api_access')) { ... }
     */
    function addon(string $addon): bool
    {
        return PlanService::hasAddon($addon);
    }
}

if (!function_exists('tenantRoute')) {
    /**
     * Generate a route URL with automatic tenant and domain injection.
     * 
     * Extracts tenant and domain from current request host and injects them
     * automatically into route parameters for multi-tenant subdomain routing.
     *
     * Usage: tenantRoute('admin.guardias.regenerate_credentials', $guardia->id)
     * 
     * @param string $name Route name
     * @param mixed $parameters Additional route parameters
     * @param bool $absolute Generate absolute URL (default: true)
     * @return string
     */
    function tenantRoute(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        // Ensure parameters is an array
        if (!is_array($parameters)) {
            $parameters = $parameters ? [$parameters] : [];
        }

        // Extract tenant and domain from current request host
        $host = request()->getHost();
        
        // For tenant routes: {tenant}.{domain} pattern
        // Example: cuarta-temuco.sas.dev-app.cl → tenant: cuarta-temuco, domain: sas.dev-app.cl
        if (tenant() && str_contains($host, '.')) {
            $parts = explode('.', $host, 2);
            if (count($parts) === 2) {
                // Only inject if not already provided
                if (!isset($parameters['tenant'])) {
                    $parameters['tenant'] = $parts[0];
                }
                if (!isset($parameters['domain'])) {
                    $parameters['domain'] = $parts[1];
                }
            }
        }

        // Fallback: use tenant() and config for missing parameters
        if (!isset($parameters['tenant']) && tenant()) {
            $parameters['tenant'] = tenant('id');
        }
        
        if (!isset($parameters['domain'])) {
            $centralDomains = config('tenancy.central_domains', []);
            if (!empty($centralDomains)) {
                $parameters['domain'] = $centralDomains[0];
            }
        }

        return route($name, $parameters, $absolute);
    }
}

