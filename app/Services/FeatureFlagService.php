<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\Tenant;

/**
 * Feature Flag Service
 *
 * Manages feature flags per tenant using Plan.php as the single source of truth.
 * 
 * Features are divided into:
 * - Modules: System operational features (voluntarios, emergencias, etc.)
 * - Addons: SaaS commercial features (api_access, custom_branding, etc.)
 * 
 * Tenant-specific overrides are stored in the tenant's `features` JSON column.
 */
class FeatureFlagService
{
    /**
     * Check if a feature (module or addon) is enabled for the current or given tenant.
     */
    public function enabled(string $feature, ?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return false;

        return (bool) $this->get($feature, $tenant);
    }

    /**
     * Check if a module is enabled.
     */
    public function moduleEnabled(string $module, ?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return false;

        // Check tenant-specific override first
        $overrides = $tenant->features ?? [];
        if (array_key_exists($module, $overrides)) {
            return (bool) $overrides[$module];
        }

        // Fall back to plan
        return $this->getPlanFeature($tenant, $module, 'module');
    }

    /**
     * Check if an addon is enabled.
     */
    public function addonEnabled(string $addon, ?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return false;

        // Check tenant-specific override first
        $overrides = $tenant->features ?? [];
        if (array_key_exists($addon, $overrides)) {
            return (bool) $overrides[$addon];
        }

        // Fall back to plan
        return $this->getPlanFeature($tenant, $addon, 'addon');
    }

    /**
     * Get a feature flag value (could be bool, int, string).
     */
    public function get(string $feature, ?Tenant $tenant = null): mixed
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return null;

        // Check tenant-specific override first
        $overrides = $tenant->features ?? [];
        if (array_key_exists($feature, $overrides)) {
            return $overrides[$feature];
        }

        // Fall back to plan (check both modules and addons)
        $plan = $this->getTenantPlan($tenant);
        if ($plan) {
            return $plan->hasFeature($feature);
        }

        // Ultimate fallback to basic plan defaults
        $defaults = Plan::getDefaultFeaturesForPlan('basico');
        if (array_key_exists($feature, $defaults)) {
            return $defaults[$feature];
        }

        $addons = Plan::getDefaultAddonsForPlan('basico');
        return $addons[$feature] ?? false;
    }

    /**
     * Set a feature flag override for a tenant.
     */
    public function set(string $feature, mixed $value, ?Tenant $tenant = null): void
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return;

        $features = $tenant->features ?? [];
        $features[$feature] = $value;
        $tenant->features = $features;
        $tenant->save();
    }

    /**
     * Remove a tenant-specific override (revert to plan default).
     */
    public function reset(string $feature, ?Tenant $tenant = null): void
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return;

        $features = $tenant->features ?? [];
        unset($features[$feature]);
        $tenant->features = $features;
        $tenant->save();
    }

    /**
     * Get all resolved features for a tenant (modules + addons with overrides).
     */
    public function all(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return [];

        $plan = $this->getTenantPlan($tenant);
        
        if ($plan) {
            $defaults = array_merge(
                $plan->features ?? [],
                $plan->addons ?? []
            );
        } else {
            // Fallback to basic plan
            $defaults = array_merge(
                Plan::getDefaultFeaturesForPlan('basico'),
                Plan::getDefaultAddonsForPlan('basico')
            );
        }

        $overrides = $tenant->features ?? [];

        return array_merge($defaults, $overrides);
    }

    /**
     * Get all modules for a tenant.
     */
    public function allModules(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return [];

        $plan = $this->getTenantPlan($tenant);
        $defaults = $plan ? ($plan->features ?? []) : Plan::getDefaultFeaturesForPlan('basico');
        $overrides = $tenant->features ?? [];

        // Only return keys that are modules
        $moduleKeys = array_keys(Plan::availableModules());
        $result = [];
        
        foreach ($moduleKeys as $key) {
            $result[$key] = $overrides[$key] ?? $defaults[$key] ?? false;
        }

        return $result;
    }

    /**
     * Get all addons for a tenant.
     */
    public function allAddons(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return [];

        $plan = $this->getTenantPlan($tenant);
        $defaults = $plan ? ($plan->addons ?? []) : Plan::getDefaultAddonsForPlan('basico');
        $overrides = $tenant->features ?? [];

        // Only return keys that are addons
        $addonKeys = array_keys(Plan::availableAddons());
        $result = [];
        
        foreach ($addonKeys as $key) {
            $result[$key] = $overrides[$key] ?? $defaults[$key] ?? false;
        }

        return $result;
    }

    /**
     * Get plan defaults for display in the central panel.
     */
    public static function planDefaults(?string $planSlug = null): array
    {
        if ($planSlug) {
            return array_merge(
                Plan::getDefaultFeaturesForPlan($planSlug),
                Plan::getDefaultAddonsForPlan($planSlug)
            );
        }

        // Return all plans
        $result = [];
        foreach (['basico', 'profesional', 'enterprise'] as $slug) {
            $result[$slug] = array_merge(
                Plan::getDefaultFeaturesForPlan($slug),
                Plan::getDefaultAddonsForPlan($slug)
            );
        }
        return $result;
    }

    /**
     * Get all available feature names (modules + addons).
     */
    public static function availableFeatures(): array
    {
        return array_merge(
            array_keys(Plan::availableModules()),
            array_keys(Plan::availableAddons())
        );
    }

    /**
     * Human-readable feature labels.
     */
    public static function featureLabels(): array
    {
        return array_merge(
            Plan::availableModules(),
            Plan::availableAddons()
        );
    }

    /**
     * Get module labels only.
     */
    public static function moduleLabels(): array
    {
        return Plan::availableModules();
    }

    /**
     * Get addon labels only.
     */
    public static function addonLabels(): array
    {
        return Plan::availableAddons();
    }

    // ==================== PRIVATE HELPERS ====================

    /**
     * Get the Plan model for a tenant.
     */
    private function getTenantPlan(Tenant $tenant): ?Plan
    {
        // If tenant has plan_id, load from database
        if ($tenant->plan_id) {
            return Plan::find($tenant->plan_id);
        }

        // Fallback: try to find plan by slug
        $planSlug = $tenant->getRawOriginal('plan') ?? 'basico';
        return Plan::where('slug', $planSlug)->first();
    }

    /**
     * Get a feature from the tenant's plan.
     */
    private function getPlanFeature(Tenant $tenant, string $feature, string $type = 'module'): bool
    {
        $plan = $this->getTenantPlan($tenant);
        
        if ($plan) {
            return $type === 'addon' ? $plan->hasAddon($feature) : $plan->hasModule($feature);
        }

        // Fallback to basic plan defaults
        if ($type === 'addon') {
            $defaults = Plan::getDefaultAddonsForPlan('basico');
        } else {
            $defaults = Plan::getDefaultFeaturesForPlan('basico');
        }

        return $defaults[$feature] ?? false;
    }
}
