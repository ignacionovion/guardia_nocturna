<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Console\Command;

class TenantCreateCommand extends Command
{
    protected $signature = 'tenant:create
                            {id : Tenant slug (e.g. tercera-temuco)}
                            {nombre : Company display name}
                            {--numero= : Company number}
                            {--body= : Body ID}
                            {--plan_id= : Plan ID from central plans table}
                            {--seed : Run DatabaseSeeder after creation}';

    protected $description = 'Create a new tenant with its database, migrations and optional domain';

    public function handle(): int
    {
        $id = $this->argument('id');

        if (Tenant::find($id)) {
            $this->error("Tenant [{$id}] already exists.");
            return self::FAILURE;
        }

        $planId = $this->option('plan_id');
        if ($planId) {
            $plan = Plan::find((int) $planId);
            if (!$plan) {
                $this->error("Plan ID [{$planId}] not found in central plans table.");
                return self::FAILURE;
            }
        } else {
            $plan = Plan::query()
                ->where('activo', true)
                ->orderBy('id')
                ->first();

            if (!$plan) {
                $this->error('No hay planes activos en la base de datos central. No se puede crear tenant.');
                return self::FAILURE;
            }

            $this->warn("No se especificó --plan_id. Se asignará automáticamente el plan [{$plan->id}] {$plan->nombre}.");
        }

        $this->info("Creating tenant [{$id}]...");

        $tenant = Tenant::create([
            'id' => $id,
            'nombre' => $this->argument('nombre'),
            'numero' => $this->option('numero') ? (int) $this->option('numero') : null,
            'body_id' => $this->option('body') ? (int) $this->option('body') : null,
            'plan_id' => $plan->id,
        ]);

        $this->info("✅ Tenant created. DB: {$tenant->tenancy_db_name}");

        // Create full domain: {tenant}.dev-app.cl
        $tenant->domains()->create(['domain' => $id . '.dev-app.cl']);
        $this->info("✅ Subdomain [{$id}] attached.");

        if ($this->option('seed')) {
            $this->call('tenants:seed', [
                '--tenants' => [$id],
            ]);

            $this->info("✅ Seeded.");
        }

        $this->newLine();
        $this->info("🎉 Tenant [{$id}] is ready. Access via http://{$id}.dev-app.cl");

        return self::SUCCESS;
    }
}
