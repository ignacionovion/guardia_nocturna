<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Plan;
use Illuminate\Console\Command;

class SyncPlansCommand extends Command
{
    protected $signature = 'plans:sync {--force : Force update even if plans exist}';
    protected $description = 'Show current plans from database (dynamic central source of truth)';

    public function handle(): int
    {
        $this->warn('plans:sync no longer seeds or overwrites plans from code.');
        $this->info('Los planes ahora son 100% dinámicos desde la base de datos central.');
        $this->newLine();

        // Show final state
        $this->info("Estado actual de los planes:");
        $this->newLine();

        $plans = Plan::orderBy('orden')->get();
        foreach ($plans as $plan) {
            $this->line("<comment>{$plan->nombre}</comment> ({$plan->slug})");
            $this->line("  Precio: $" . number_format((float) $plan->precio_mensual, 0, ',', '.') . "/mes");
            $this->line("  Límites: {$plan->max_users} usuarios, {$plan->max_volunteers} voluntarios, {$plan->max_guardias} guardias, {$plan->max_beds} camas");
            
            $enabledModules = $plan->getEnabledModules();
            $this->line("  Módulos: " . (empty($enabledModules) ? 'ninguno' : implode(', ', $enabledModules)));
            
            $enabledAddons = $plan->getEnabledAddons();
            $this->line("  Addons: " . (empty($enabledAddons) ? 'ninguno' : implode(', ', $enabledAddons)));
            
            $this->newLine();
        }

        return Command::SUCCESS;
    }
}
