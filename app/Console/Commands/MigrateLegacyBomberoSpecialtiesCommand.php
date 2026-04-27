<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Bombero;
use App\Models\Specialty;
use Illuminate\Console\Command;

class MigrateLegacyBomberoSpecialtiesCommand extends Command
{
    protected $signature = 'bomberos:migrate-legacy-specialties {--dry-run : Solo muestra cambios sin escribir en BD}';

    protected $description = 'Migra flags legacy de bomberos a especialidades tenant (idempotente)';

    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $mapping = [
            'es_conductor' => 'Conductor',
            'es_operador_rescate' => 'Operador Rescate',
            'es_asistente_trauma' => 'Asistente Trauma',
        ];

        $createdSpecialties = 0;
        $attachedLinks = 0;

        foreach ($mapping as $legacyColumn => $specialtyName) {
            $specialty = Specialty::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($specialtyName)])->first();
            if (!$specialty && !$isDryRun) {
                $specialty = Specialty::query()->create([
                    'name' => $specialtyName,
                    'slug' => \Illuminate\Support\Str::slug($specialtyName),
                    'icon' => 'fas fa-star',
                    'color' => '#64748b',
                    'active' => true,
                ]);
                $createdSpecialties++;
            }

            if (!$specialty) {
                $this->warn("Especialidad '{$specialtyName}' no existe (dry-run).");
                continue;
            }

            $bomberos = Bombero::query()
                ->where($legacyColumn, true)
                ->with('specialties:id')
                ->get();

            foreach ($bomberos as $bombero) {
                $alreadyAttached = $bombero->specialties->contains('id', $specialty->id);
                if ($alreadyAttached) {
                    continue;
                }

                if (!$isDryRun) {
                    $bombero->specialties()->syncWithoutDetaching([$specialty->id]);
                }
                $attachedLinks++;
            }

            $this->line("Migrado {$legacyColumn} -> {$specialtyName}: {$bomberos->count()} bomberos revisados.");
        }

        $this->info('Migración finalizada.');
        $this->info("Especialidades creadas: {$createdSpecialties}");
        $this->info("Relaciones agregadas: {$attachedLinks}");

        return self::SUCCESS;
    }
}
