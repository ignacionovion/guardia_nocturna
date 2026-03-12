<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Plan;
use Illuminate\Console\Command;

class SyncPlansCommand extends Command
{
    protected $signature = 'plans:sync {--force : Force update even if plans exist}';
    protected $description = 'Sync plans table with Plan::defaultPlans() definitions';

    public function handle(): int
    {
        $this->info('Sincronizando planes con las definiciones de Plan::defaultPlans()...');
        $this->newLine();

        $defaultPlans = Plan::defaultPlans();
        $updated = 0;
        $created = 0;

        foreach ($defaultPlans as $planData) {
            $plan = Plan::where('slug', $planData['slug'])->first();

            if ($plan) {
                // Show current vs new
                $this->line("Plan: <comment>{$planData['nombre']}</comment>");
                
                $changes = [];
                
                // Check features changes
                $currentFeatures = $plan->features ?? [];
                $newFeatures = $planData['features'];
                $featureChanges = $this->compareArrays($currentFeatures, $newFeatures);
                if (!empty($featureChanges)) {
                    $changes['features'] = $featureChanges;
                }

                // Check addons changes
                $currentAddons = $plan->addons ?? [];
                $newAddons = $planData['addons'] ?? [];
                $addonChanges = $this->compareArrays($currentAddons, $newAddons);
                if (!empty($addonChanges)) {
                    $changes['addons'] = $addonChanges;
                }

                // Check limits changes
                foreach (['max_users', 'max_guardias', 'max_beds', 'max_storage_mb', 'precio_mensual'] as $field) {
                    if ($plan->$field !== $planData[$field]) {
                        $changes[$field] = [
                            'from' => $plan->$field,
                            'to' => $planData[$field],
                        ];
                    }
                }

                if (!empty($changes)) {
                    $this->table(['Campo', 'Antes', 'Después'], $this->formatChangesTable($changes));
                    
                    if ($this->option('force') || $this->confirm("¿Actualizar plan '{$planData['nombre']}'?", true)) {
                        $plan->update([
                            'features' => $planData['features'],
                            'addons' => $planData['addons'] ?? [],
                            'max_users' => $planData['max_users'],
                            'max_guardias' => $planData['max_guardias'],
                            'max_beds' => $planData['max_beds'],
                            'max_storage_mb' => $planData['max_storage_mb'],
                            'precio_mensual' => $planData['precio_mensual'],
                        ]);
                        $this->info("  ✓ Plan actualizado");
                        $updated++;
                    } else {
                        $this->line("  → Omitido");
                    }
                } else {
                    $this->info("  ✓ Sin cambios necesarios");
                }
            } else {
                // Create new plan
                $this->line("Plan: <comment>{$planData['nombre']}</comment> (nuevo)");
                Plan::create($planData);
                $this->info("  ✓ Plan creado");
                $created++;
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info("Resumen:");
        $this->line("  - Planes actualizados: {$updated}");
        $this->line("  - Planes creados: {$created}");
        $this->newLine();

        // Show final state
        $this->info("Estado actual de los planes:");
        $this->newLine();

        $plans = Plan::orderBy('orden')->get();
        foreach ($plans as $plan) {
            $this->line("<comment>{$plan->nombre}</comment> ({$plan->slug})");
            $this->line("  Precio: $" . number_format((float) $plan->precio_mensual, 0, ',', '.') . "/mes");
            $this->line("  Límites: {$plan->max_users} usuarios, {$plan->max_guardias} guardias, {$plan->max_beds} camas");
            
            $enabledModules = $plan->getEnabledModules();
            $this->line("  Módulos: " . (empty($enabledModules) ? 'ninguno' : implode(', ', $enabledModules)));
            
            $enabledAddons = $plan->getEnabledAddons();
            $this->line("  Addons: " . (empty($enabledAddons) ? 'ninguno' : implode(', ', $enabledAddons)));
            
            $this->newLine();
        }

        return Command::SUCCESS;
    }

    private function compareArrays(array $current, array $new): array
    {
        $changes = [];
        
        foreach ($new as $key => $value) {
            if (!array_key_exists($key, $current)) {
                $changes[$key] = ['from' => '(no existe)', 'to' => $value ? 'true' : 'false'];
            } elseif ($current[$key] !== $value) {
                $changes[$key] = ['from' => $current[$key] ? 'true' : 'false', 'to' => $value ? 'true' : 'false'];
            }
        }

        // Check for removed keys
        foreach ($current as $key => $value) {
            if (!array_key_exists($key, $new)) {
                $changes[$key] = ['from' => $value ? 'true' : 'false', 'to' => '(eliminado)'];
            }
        }

        return $changes;
    }

    private function formatChangesTable(array $changes): array
    {
        $rows = [];
        
        foreach ($changes as $field => $data) {
            if (is_array($data) && isset($data['from']) && isset($data['to'])) {
                $rows[] = [$field, (string) $data['from'], (string) $data['to']];
            } else {
                foreach ($data as $subField => $subData) {
                    $rows[] = ["{$field}.{$subField}", (string) $subData['from'], (string) $subData['to']];
                }
            }
        }

        return $rows;
    }
}
