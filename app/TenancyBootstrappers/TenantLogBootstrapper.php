<?php

declare(strict_types=1);

namespace App\TenancyBootstrappers;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

/**
 * Redirects the default log channel to a tenant-specific log file
 * when a tenant is initialized. Reverts on tenancy end.
 */
class TenantLogBootstrapper implements TenancyBootstrapper
{
    protected ?string $originalPath = null;

    public function bootstrap(Tenant $tenant): void
    {
        $this->originalPath = config('logging.channels.single.path');

        $tenantLogPath = storage_path("logs/tenants/{$tenant->getTenantKey()}.log");

        // Ensure directory exists
        $dir = dirname($tenantLogPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        config([
            'logging.channels.single.path' => $tenantLogPath,
            'logging.channels.daily.path'  => $tenantLogPath,
        ]);
    }

    public function revert(): void
    {
        if ($this->originalPath) {
            config([
                'logging.channels.single.path' => $this->originalPath,
                'logging.channels.daily.path'  => $this->originalPath,
            ]);
        }
    }
}
