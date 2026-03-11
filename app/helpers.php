<?php

use App\Services\FeatureFlagService;

if (!function_exists('feature')) {
    /**
     * Check if a feature is enabled for the current tenant.
     *
     * Usage in views: @if(feature('inventario')) ... @endif
     * Usage in code:  if (feature('max_users')) { ... }
     */
    function feature(string $feature, mixed $default = false): mixed
    {
        $service = app(FeatureFlagService::class);
        $value = $service->get($feature);

        return $value ?? $default;
    }
}
