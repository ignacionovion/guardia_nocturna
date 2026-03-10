<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantRunCommand extends Command
{
    protected $signature = 'tenant:run
                            {command : The artisan command to run for each tenant}
                            {--tenant= : Run only for a specific tenant ID}';

    protected $description = 'Run an artisan command within the context of each active tenant';

    public function handle(): int
    {
        $commandToRun = $this->argument('command');
        $specificTenant = $this->option('tenant');

        $query = Tenant::where('activo', true);

        if ($specificTenant) {
            $query->where('id', $specificTenant);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');
            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $this->info("Running [{$commandToRun}] for tenant [{$tenant->id}]...");

            $tenant->run(function () use ($commandToRun) {
                Artisan::call($commandToRun);
            });
        }

        $this->info("Done. Ran [{$commandToRun}] for {$tenants->count()} tenant(s).");

        return self::SUCCESS;
    }
}
