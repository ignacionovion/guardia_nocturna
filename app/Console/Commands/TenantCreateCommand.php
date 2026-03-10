<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class TenantCreateCommand extends Command
{
    protected $signature = 'tenant:create
                            {id : Tenant slug (e.g. tercera-temuco)}
                            {nombre : Company display name}
                            {domain : Full domain (e.g. tercera-temuco.dev-app.cl)}
                            {--numero= : Company number}
                            {--body= : Body ID}
                            {--plan=basico : Plan (basico|profesional|enterprise)}
                            {--seed : Run DatabaseSeeder after creation}';

    protected $description = 'Create a new tenant with its database, migrations and optional domain';

    public function handle(): int
    {
        $id = $this->argument('id');

        if (Tenant::find($id)) {
            $this->error("Tenant [{$id}] already exists.");
            return self::FAILURE;
        }

        $this->info("Creating tenant [{$id}]...");

        $tenant = Tenant::create([
            'id' => $id,
            'nombre' => $this->argument('nombre'),
            'numero' => $this->option('numero') ? (int) $this->option('numero') : null,
            'body_id' => $this->option('body') ? (int) $this->option('body') : null,
            'plan' => $this->option('plan'),
        ]);

        $this->info("✅ Tenant created. DB: {$tenant->tenancy_db_name}");

        $domain = $this->argument('domain');
        $tenant->domains()->create(['domain' => $domain]);
        $this->info("✅ Domain [{$domain}] attached.");

        if ($this->option('seed')) {
            $this->info("Seeding tenant database...");
            $tenant->run(function () {
                $seeder = new \Database\Seeders\DatabaseSeeder();
                $seeder->run();
            });
            $this->info("✅ Tenant database seeded.");
        }

        $this->newLine();
        $this->info("🎉 Tenant [{$id}] is ready at https://{$domain}");

        return self::SUCCESS;
    }
}
