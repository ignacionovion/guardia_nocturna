<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixTenantPlansCommand extends Command
{
    protected $signature = 'tenants:fix-plans {--dry-run : Solo mostrar cambios sin persistir}';

    protected $description = 'Repara tenants sin plan_id o con plan inválido asignando el primer plan activo disponible';

    public function handle(): int
    {
        $fallbackPlan = Plan::query()
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$fallbackPlan) {
            $this->error('No hay planes activos en la base de datos central. No se pueden reparar tenants.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $tenants = Tenant::query()
            ->with('planRelation:id')
            ->get()
            ->filter(function (Tenant $tenant): bool {
                return !$tenant->plan_id || !$tenant->planRelation;
            })
            ->values();

        if ($tenants->isEmpty()) {
            $this->info('No se encontraron tenants para reparar.');
            return self::SUCCESS;
        }

        $this->warn("Plan de reparación: [{$fallbackPlan->id}] {$fallbackPlan->nombre}");
        $this->line("Tenants afectados: {$tenants->count()}");

        $fixed = 0;

        foreach ($tenants as $tenant) {
            $context = [
                'tenant_id' => $tenant->id,
                'previous_plan_id' => $tenant->plan_id,
                'new_plan_id' => $fallbackPlan->id,
                'dry_run' => $dryRun,
            ];

            if ($dryRun) {
                $this->line("[DRY-RUN] {$tenant->id}: {$tenant->plan_id} -> {$fallbackPlan->id}");
                Log::info('tenant_plan_fix_preview', $context);
                continue;
            }

            $tenant->update(['plan_id' => $fallbackPlan->id]);
            $fixed++;

            $this->info("✓ {$tenant->id}: plan_id actualizado a {$fallbackPlan->id}");
            Log::info('tenant_plan_fixed', $context);
        }

        if ($dryRun) {
            $this->warn('Modo DRY-RUN: no se persistieron cambios.');
            return self::SUCCESS;
        }

        $this->info("Reparación completada. Tenants actualizados: {$fixed}");
        return self::SUCCESS;
    }
}
