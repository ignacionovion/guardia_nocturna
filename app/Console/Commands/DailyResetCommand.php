<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DailyResetService;
use Illuminate\Console\Command;

class DailyResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guardia:daily-reset 
                            {--guardia= : ID específico de guardia para resetear (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta el reset diario de reemplazos/refuerzos y libera camas asignadas (07:00 AM)';

    /**
     * Execute the console command.
     */
    public function handle(DailyResetService $service): int
    {
        $this->info('Iniciando reset diario de Guardia Nocturna...');

        $guardiaId = $this->option('guardia');

        if ($guardiaId) {
            $this->info("Reseteando guardia ID: {$guardiaId}");
            $result = $service->executeResetForGuardia((int) $guardiaId);
            $this->displayResults(['Guardia específica' => $result]);
        } else {
            $this->info('Reseteando todas las guardias...');
            $results = $service->executeDailyReset();
            $this->displayResults($results);
        }

        $this->info('Reset diario completado.');

        return self::SUCCESS;
    }

    private function displayResults(array $results): void
    {
        foreach ($results as $guardiaName => $stats) {
            $this->info("\n--- {$guardiaName} ---");
            $this->table(
                ['Métrica', 'Valor'],
                [
                    ['Hora corte', $stats['cutoff_time'] ?? 'N/A'],
                    ['Bomberos reseteados', $stats['bomberos_reseteados'] ?? 0],
                    ['Camas liberadas', $stats['camas_liberadas'] ?? 0],
                    ['Reemplazos completados', $stats['reemplazos_completados'] ?? 0],
                    ['Titulares reseteados', $stats['titulares_reseteados'] ?? 0],
                ]
            );
        }
    }
}
