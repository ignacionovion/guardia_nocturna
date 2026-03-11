<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;

/**
 * Feature Flag Service
 *
 * Manages feature flags per tenant. Flags are stored in the tenant's
 * `data` JSON column (provided by stancl/tenancy) under the key 'features'.
 *
 * Plan-based defaults are used when a tenant hasn't explicitly set a flag.
 */
class FeatureFlagService
{
    /**
     * Default features per plan.
     * true = enabled, false = disabled.
     */
    protected static array $planDefaults = [
        'basico' => [
            'emergencias'       => true,
            'guardias'          => true,
            'camas'             => true,
            'inventario'        => false,
            'dotaciones'        => false,
            'reportes'          => false,
            'api_access'        => false,
            'custom_branding'   => false,
            'backups'           => false,
            'max_users'         => 10,
        ],
        'profesional' => [
            'emergencias'       => true,
            'guardias'          => true,
            'camas'             => true,
            'inventario'        => true,
            'dotaciones'        => true,
            'reportes'          => true,
            'api_access'        => false,
            'custom_branding'   => false,
            'backups'           => true,
            'max_users'         => 50,
        ],
        'enterprise' => [
            'emergencias'       => true,
            'guardias'          => true,
            'camas'             => true,
            'inventario'        => true,
            'dotaciones'        => true,
            'reportes'          => true,
            'api_access'        => true,
            'custom_branding'   => true,
            'backups'           => true,
            'max_users'         => -1, // unlimited
        ],
    ];

    /**
     * Check if a feature is enabled for the current or given tenant.
     */
    public function enabled(string $feature, ?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return false;

        $value = $this->get($feature, $tenant);

        return (bool) $value;
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

        // Fall back to plan defaults
        $plan = $tenant->plan ?? 'basico';
        return static::$planDefaults[$plan][$feature] ?? null;
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
     * Get all resolved features for a tenant (overrides merged with plan defaults).
     */
    public function all(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? tenant();
        if (!$tenant) return [];

        $plan = $tenant->plan ?? 'basico';
        $defaults = static::$planDefaults[$plan] ?? static::$planDefaults['basico'];
        $overrides = $tenant->features ?? [];

        return array_merge($defaults, $overrides);
    }

    /**
     * Get plan defaults for display in the central panel.
     */
    public static function planDefaults(?string $plan = null): array
    {
        if ($plan) {
            return static::$planDefaults[$plan] ?? [];
        }
        return static::$planDefaults;
    }

    /**
     * Get all available feature names.
     */
    public static function availableFeatures(): array
    {
        return array_keys(static::$planDefaults['enterprise']);
    }

    /**
     * Human-readable feature labels.
     */
    public static function featureLabels(): array
    {
        return [
            'emergencias'     => 'Emergencias',
            'guardias'        => 'Guardias Nocturnas',
            'camas'           => 'Gestión de Camas',
            'inventario'      => 'Inventario',
            'dotaciones'      => 'Dotaciones',
            'reportes'        => 'Reportes Avanzados',
            'api_access'      => 'Acceso API',
            'custom_branding' => 'Marca Personalizada',
            'backups'         => 'Backups Automáticos',
            'max_users'       => 'Máx. Usuarios',
        ];
    }
}
