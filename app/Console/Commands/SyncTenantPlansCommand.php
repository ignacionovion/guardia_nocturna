<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Plan;

class SyncTenantPlansCommand extends Command
{
    protected $signature = 'tenant:sync-plans {--dry-run : Solo mostrar, no actualizar}';
    protected $description = 'Sync tenant plan_id with plan slug for existing tenants';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('=== Sincronización de Planes de Tenants ===');
        $this->newLine();

        $tenants = Tenant::all();
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($tenants as $tenant) {
            $planSlug = $tenant->plan ?? 'basico';
            $plan = Plan::where('slug', $planSlug)->first();

            if (!$plan) {
                $this->error("Plan no encontrado: {$planSlug} (tenant: {$tenant->id})");
                $errors++;
                continue;
            }

            if ($tenant->plan_id === $plan->id) {
                $this->line("✓ {$tenant->id}: ya sincronizado ({$planSlug})");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->info("[DRY-RUN] {$tenant->id}: plan_id=null → {$plan->id} ({$planSlug})");
            } else {
                $tenant->update(['plan_id' => $plan->id]);
                $this->info("✓ {$tenant->id}: plan_id actualizado a {$plan->id} ({$planSlug})");
            }
            $updated++;
        }

        $this->newLine();
        $this->info("Resumen:");
        $this->info("  Actualizados: {$updated}");
        $this->info("  Ya sincronizados: {$skipped}");
        $this->info("  Errores: {$errors}");

        return 0;
    }
}
