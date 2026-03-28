<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\Plan;

class SyncTenantPlansCommand extends Command
{
    protected $signature = 'tenant:sync-plans {--dry-run : Solo mostrar, no actualizar}';
    protected $description = 'Sync missing tenant plan_id from existing central records';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('=== Sincronización de Planes de Tenants ===');
        $this->newLine();

        $tenants = Tenant::with('billing')->get();
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($tenants as $tenant) {
            if ($tenant->plan_id) {
                $this->line("✓ {$tenant->id}: ya tiene plan_id={$tenant->plan_id}");
                $skipped++;
                continue;
            }

            $plan = null;

            // Preferred source: billing.plan_id
            $billingPlanId = $tenant->billing?->plan_id;
            if ($billingPlanId) {
                $plan = Plan::find((int) $billingPlanId);
            }

            // Secondary source: billing.plan slug
            if (!$plan && !empty($tenant->billing?->plan)) {
                $plan = Plan::where('slug', $tenant->billing->plan)->first();
            }

            if (!$plan) {
                $this->error("No se pudo resolver plan para tenant {$tenant->id} (sin plan_id ni billing mapeable)");
                $errors++;
                continue;
            }

            if ($dryRun) {
                $this->info("[DRY-RUN] {$tenant->id}: plan_id=null → {$plan->id} ({$plan->slug})");
            } else {
                $tenant->update(['plan_id' => $plan->id]);
                $this->info("✓ {$tenant->id}: plan_id actualizado a {$plan->id} ({$plan->slug})");
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
