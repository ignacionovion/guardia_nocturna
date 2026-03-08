<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Bombero;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class ImportBomberosFotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-bomberos-fotos
                            {directory : Ruta absoluta o relativa a la carpeta con imágenes}
                            {--dry-run : Solo muestra qué haría, sin guardar cambios}
                            {--replace : Reemplaza fotos existentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa fotos de bomberos en lote usando el RUT en el nombre del archivo';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $directoryArgument = (string) $this->argument('directory');
        $directory = $this->resolveDirectory($directoryArgument);
        $dryRun = (bool) $this->option('dry-run');
        $replace = (bool) $this->option('replace');

        if (!is_dir($directory)) {
            $this->error("La carpeta no existe: {$directory}");
            return self::FAILURE;
        }

        $finder = new Finder();
        $finder->files()->in($directory)->ignoreUnreadableDirs()->name('/\.(jpg|jpeg|png|webp)$/i');

        if (!$finder->hasResults()) {
            $this->warn('No se encontraron imágenes válidas en la carpeta indicada.');
            return self::SUCCESS;
        }

        $bomberos = Bombero::query()
            ->get(['id', 'rut', 'nombres', 'apellido_paterno', 'photo_path'])
            ->filter(fn (Bombero $bombero) => filled($bombero->rut) && filled($this->normalizeRut($bombero->rut)))
            ->keyBy(fn (Bombero $bombero) => $this->normalizeRut($bombero->rut));

        if ($bomberos->isEmpty()) {
            $this->warn('No hay bomberos con RUT cargado para asociar imágenes.');
            return self::SUCCESS;
        }

        $processed = 0;
        $imported = 0;
        $skipped = 0;
        $missing = 0;
        $errors = 0;

        foreach ($finder as $file) {
            $processed++;

            $originalName = $file->getFilename();
            $rutFromFile = $this->extractRutFromFilename($file->getBasename('.' . $file->getExtension()));

            if (!$rutFromFile) {
                $missing++;
                $this->warn("[SIN RUT] {$originalName}");
                continue;
            }

            /** @var Bombero|null $bombero */
            $bombero = $bomberos->get($rutFromFile);

            if (!$bombero) {
                $missing++;
                $this->warn("[NO ENCONTRADO] {$originalName} -> {$rutFromFile}");
                continue;
            }

            if ($bombero->photo_path && !$replace) {
                $skipped++;
                $this->line("[OMITIDA] {$bombero->rut} - {$bombero->nombres} {$bombero->apellido_paterno} ya tiene foto");
                continue;
            }

            $extension = strtolower($file->getExtension());
            $targetPath = 'bomberos/' . $rutFromFile . '.' . $extension;

            try {
                if (!$dryRun) {
                    if ($replace && $bombero->photo_path && $bombero->photo_path !== $targetPath) {
                        Storage::disk('public')->delete($bombero->photo_path);
                    }

                    Storage::disk('public')->put($targetPath, $file->getContents());

                    $bombero->update([
                        'photo_path' => $targetPath,
                    ]);
                }

                $imported++;
                $this->info("[OK] {$bombero->rut} - {$bombero->nombres} {$bombero->apellido_paterno} <- {$originalName}");
            } catch (\Throwable $e) {
                $errors++;
                $this->error("[ERROR] {$originalName}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->table(
            ['Procesadas', 'Importadas', 'Omitidas', 'Sin match', 'Errores', 'Modo'],
            [[
                $processed,
                $imported,
                $skipped,
                $missing,
                $errors,
                $dryRun ? 'dry-run' : 'real',
            ]]
        );

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolveDirectory(string $directory): string
    {
        if (Str::startsWith($directory, ['/'])) {
            return $directory;
        }

        return base_path($directory);
    }

    private function extractRutFromFilename(string $filename): ?string
    {
        $normalized = strtoupper((string) preg_replace('/[^0-9Kk]/', '', $filename));

        if (strlen($normalized) < 2) {
            return null;
        }

        $body = substr($normalized, 0, -1);
        $dv = substr($normalized, -1);

        if ($body === '' || !ctype_digit($body) || !preg_match('/^[0-9K]$/', $dv)) {
            return null;
        }

        return ltrim($body, '0') . $dv;
    }

    private function normalizeRut(?string $rut): ?string
    {
        if (!$rut) {
            return null;
        }

        $normalized = strtoupper((string) preg_replace('/[^0-9Kk]/', '', $rut));

        if (strlen($normalized) < 2) {
            return null;
        }

        $body = substr($normalized, 0, -1);
        $dv = substr($normalized, -1);

        if ($body === '' || !ctype_digit($body) || !preg_match('/^[0-9K]$/', $dv)) {
            return null;
        }

        return ltrim($body, '0') . $dv;
    }
}
