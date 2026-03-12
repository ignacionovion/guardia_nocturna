<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigratePlanFeaturesCommand extends Command
{
    protected $signature = 'plan:migrate-features 
                            {--dry-run : Simular sin aplicar cambios}
                            {--tenant= : Migrar solo un tenant específico}';

    protected $description = 'Migrar features legacy a nueva estructura de módulos y add-ons';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $specificTenant = $this->option('tenant');

        $this->info('=== Migración de Features Legacy ===');
        if ($dryRun) {
            $this->warn('MODO SIMULACIÓN - No se aplicarán cambios');
        }

        // 1. Migrar tabla plans
        $this->migratePlansTable($dryRun);

        // 2. Migrar tenants
        $this->migrateTenants($dryRun, $specificTenant);

        // 3. Verificar integridad
        $this->verifyIntegrity();

        $this->info('=== Migración completada ===');

        return self::SUCCESS;
    }

    private function migratePlansTable(bool $dryRun): void
    {
        $this->info("\nMigrando tabla plans...");

        $plans = Plan::all();
        $bar = $this->output->createProgressBar($plans->count());

        foreach ($plans as $plan) {
            $oldFeatures = $plan->features ?? [];

            // Mapeo de features legacy a nueva estructura
            $newModules = $this->mapToModules($oldFeatures, $plan->slug);
            $newAddons = $this->mapToAddons($oldFeatures);

            if (!$dryRun) {
                DB::table('plans')
                    ->where('id', $plan->id)
                    ->update([
                        'features' => json_encode($newModules),
                        'addons' => json_encode($newAddons),
                        'updated_at' => now(),
                    ]);
            }

            $this->logChange("Plan {$plan->slug}", $oldFeatures, [
                'modules' => $newModules,
                'addons' => $newAddons,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    private function migrateTenants(bool $dryRun, ?string $specificTenant): void
    {
        $this->info("\nVerificando tenants...");

        $query = Tenant::query();
        if ($specificTenant) {
            $query->where('id', $specificTenant);
        }

        $tenants = $query->get();
        $bar = $this->output->createProgressBar($tenants->count());

        $fixed = 0;
        $errors = 0;

        foreach ($tenants as $tenant) {
            try {
                // Verificar si tiene plan_id válido
                if ($tenant->plan_id && !Plan::find($tenant->plan_id)) {
                    $this->warn("Tenant {$tenant->id} tiene plan_id inválido: {$tenant->plan_id}");

                    // Asignar plan basado en campo legacy
                    $legacyPlan = $tenant->getRawOriginal('plan') ?? 'basico';
                    $newPlan = Plan::where('slug', $legacyPlan)->first();

                    if ($newPlan && !$dryRun) {
                        $tenant->update(['plan_id' => $newPlan->id]);
                        $this->info("  → Asignado plan {$newPlan->slug}");
                        $fixed++;
                    }
                }

                // Sincronizar campo legacy con plan_id
                if (!$tenant->plan_id && $tenant->plan) {
                    $plan = Plan::where('slug', $tenant->plan)->first();
                    if ($plan && !$dryRun) {
                        $tenant->update(['plan_id' => $plan->id]);
                        $fixed++;
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error en tenant {$tenant->id}: " . $e->getMessage());
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Fixed: {$fixed}, Errors: {$errors}");
    }

    private function verifyIntegrity(): void
    {
        $this->info("\nVerificando integridad...");

        // Contar planes por tipo
        $counts = Plan::selectRaw('slug, count(*) as count')
            ->groupBy('slug')
            ->pluck('count', 'slug');

        foreach ($counts as $slug => $count) {
            $this->info("  Plan {$slug}: {$count} registros");
        }

        // Verificar tenants sin plan
        $orphanTenants = Tenant::whereNull('plan_id')
            ->whereNull('plan')
            ->count();

        if ($orphanTenants > 0) {
            $this->warn("  ⚠️  {$orphanTenants} tenants sin plan asignado");
        } else {
            $this->info("  ✓ Todos los tenants tienen plan asignado");
        }
    }

    private function mapToModules(array $legacyFeatures, string $planSlug): array
    {
        // Defaults por plan
        $defaults = [
            'basico' => ['voluntarios', 'emergencias', 'dotaciones', 'calendario', 'guardia', 'camas'],
            'profesional' => ['voluntarios', 'emergencias', 'dotaciones', 'calendario', 'guardia', 'camas', 'reportes', 'planilla'],
            'enterprise' => ['voluntarios', 'emergencias', 'dotaciones', 'calendario', 'guardia', 'camas', 'reportes', 'planilla', 'now', 'preventiva', 'inventario'],
        ];

        $modules = array_fill_keys(array_keys(Plan::availableModules()), false);

        // Activar defaults del plan
        foreach ($defaults[$planSlug] ?? $defaults['basico'] as $module) {
            $modules[$module] = true;
        }

        // Aplicar mapeo legacy
        $mapping = [
            'reportes_avanzados' => ['reportes', 'planilla'],
            'estadisticas_avanzadas' => ['reportes'],
        ];

        foreach ($legacyFeatures as $feature => $enabled) {
            if ($enabled && isset($mapping[$feature])) {
                foreach ($mapping[$feature] as $module) {
                    $modules[$module] = true;
                }
            }
        }

        return $modules;
    }

    private function mapToAddons(array $legacyFeatures): array
    {
        $addons = array_fill_keys(array_keys(Plan::availableAddons()), false);

        $mapping = [
            'api_access' => 'api_access',
            'custom_branding' => 'custom_branding',
            'priority_support' => 'priority_support',
            'audit_logs' => 'audit_logs',
            'whatsapp_integration' => null, // No existe en nueva estructura
            'advanced_notifications' => null,
        ];

        foreach ($legacyFeatures as $feature => $enabled) {
            if ($enabled && isset($mapping[$feature]) && $mapping[$feature]) {
                $addons[$mapping[$feature]] = true;
            }
        }

        return $addons;
    }

    private function logChange(string $entity, array $old, array $new): void
    {
        if ($this->output->isVerbose()) {
            $this->line("  {$entity}:");
            $this->line("    Old: " . json_encode($old));
            $this->line("    New: " . json_encode($new));
        }
    }
}
