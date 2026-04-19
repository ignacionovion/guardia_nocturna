<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\OperationalHealthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantRunCommand extends Command
{
    protected $signature = 'tenant:run
                            {tenantCommand : The artisan command to run for each tenant}
                            {--tenant= : Run only for a specific tenant ID}';

    protected $description = 'Run an artisan command within the context of each active tenant';

    public function handle(): int
    {
        $ops = app(OperationalHealthService::class);
        $commandToRun = $this->argument('tenantCommand');
        $specificTenant = $this->option('tenant');

        $query = Tenant::where('activo', true);

        if ($specificTenant) {
            $query->where('id', $specificTenant);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');
            $ops->recordTenantRun($commandToRun, [
                'tenants' => 0,
                'failed_inner' => 0,
                'exit_code' => 0,
                'note' => 'no_active_tenants',
            ]);

            return self::SUCCESS;
        }

        $failedInner = 0;
        foreach ($tenants as $tenant) {
            $this->info("Running [{$commandToRun}] for tenant [{$tenant->id}]...");

            $tenant->run(function () use ($commandToRun, &$failedInner) {
                $code = Artisan::call($commandToRun);
                if ($code !== 0) {
                    $failedInner++;
                }
            });
        }

        $exitCode = $failedInner > 0 ? 1 : 0;
        $ops->recordTenantRun($commandToRun, [
            'tenants' => $tenants->count(),
            'failed_inner' => $failedInner,
            'exit_code' => $exitCode,
        ]);

        $this->info("Done. Ran [{$commandToRun}] for {$tenants->count()} tenant(s).");

        return $exitCode !== 0 ? self::FAILURE : self::SUCCESS;
    }
}
