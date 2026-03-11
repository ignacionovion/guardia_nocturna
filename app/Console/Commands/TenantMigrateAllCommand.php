<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TenantMigrateAllCommand extends Command
{
    protected $signature = 'tenant:migrate-all
                            {--seed : Also run seeders after migration}
                            {--fresh : Drop all tables and re-run migrations (DANGEROUS)}
                            {--tenant= : Run only for a specific tenant ID}
                            {--force : Force the operation in production}';

    protected $description = 'Run migrations for all active tenants with detailed status output';

    public function handle(): int
    {
        $specificTenant = $this->option('tenant');
        $fresh = $this->option('fresh');
        $seed = $this->option('seed');
        $force = $this->option('force');

        if ($fresh && !$this->confirm('⚠️  --fresh will DROP ALL TABLES for each tenant. Are you sure?')) {
            return self::FAILURE;
        }

        $query = Tenant::where('activo', true);
        if ($specificTenant) {
            $query->where('id', $specificTenant);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found.');
            return self::SUCCESS;
        }

        $this->info("Found {$tenants->count()} tenant(s) to migrate.");
        $this->newLine();

        $results = [];

        foreach ($tenants as $tenant) {
            $this->info("━━━ Tenant: {$tenant->id} ({$tenant->nombre}) ━━━");

            $dbName = $tenant->database()->getName();
            $dbExists = $this->databaseExists($dbName);

            if (!$dbExists) {
                $this->error("  ✗ Database '{$dbName}' does not exist. Skipping.");
                $results[] = ['tenant' => $tenant->id, 'status' => 'SKIP', 'reason' => 'No DB'];
                $this->newLine();
                continue;
            }

            try {
                $migrationsBefore = $this->getMigrationCount($tenant);

                $command = $fresh ? 'migrate:fresh' : 'migrate';
                $params = ['--force' => true, '--path' => 'database/migrations/tenant'];

                if ($seed) {
                    $params['--seed'] = true;
                }

                $tenant->run(function () use ($command, $params) {
                    Artisan::call($command, $params);
                });

                $migrationsAfter = $this->getMigrationCount($tenant);
                $newMigrations = $migrationsAfter - $migrationsBefore;

                if ($newMigrations > 0) {
                    $this->info("  ✓ {$newMigrations} new migration(s) applied (total: {$migrationsAfter})");
                } else {
                    $this->info("  ✓ Already up to date ({$migrationsAfter} migrations)");
                }

                if ($seed) {
                    $this->info("  ✓ Seeders executed");
                }

                $results[] = ['tenant' => $tenant->id, 'status' => 'OK', 'reason' => "+{$newMigrations}"];
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed: {$e->getMessage()}");
                $results[] = ['tenant' => $tenant->id, 'status' => 'FAIL', 'reason' => $e->getMessage()];
            }

            $this->newLine();
        }

        // Summary table
        $this->info('━━━ Summary ━━━');
        $this->table(
            ['Tenant', 'Status', 'Detail'],
            array_map(fn ($r) => [$r['tenant'], $r['status'], \Illuminate\Support\Str::limit($r['reason'], 60)], $results)
        );

        $ok = count(array_filter($results, fn ($r) => $r['status'] === 'OK'));
        $fail = count(array_filter($results, fn ($r) => $r['status'] === 'FAIL'));
        $skip = count(array_filter($results, fn ($r) => $r['status'] === 'SKIP'));

        $this->info("Done: {$ok} OK, {$fail} failed, {$skip} skipped.");

        return $fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function databaseExists(string $name): bool
    {
        try {
            $result = DB::connection('central')->select(
                "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
                [$name]
            );
            return count($result) > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function getMigrationCount(Tenant $tenant): int
    {
        try {
            return (int) $tenant->run(function () {
                return DB::table('migrations')->count();
            });
        } catch (\Throwable) {
            return 0;
        }
    }
}
