<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOrphanedDomainsCommand extends Command
{
    protected $signature = 'tenant:cleanup-domains {--dry-run : Solo mostrar, no eliminar}';
    protected $description = 'Clean up orphaned domains (domains without associated tenants)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('=== Limpieza de Dominios Huérfanos ===');
        $this->newLine();

        // Find orphaned domains
        $orphaned = DB::connection('central')->select('
            SELECT d.id, d.domain, d.tenant_id, d.created_at
            FROM domains d 
            LEFT JOIN tenants t ON d.tenant_id = t.id 
            WHERE t.id IS NULL
            ORDER BY d.created_at DESC
        ');

        if (count($orphaned) === 0) {
            $this->info('No se encontraron dominios huérfanos.');
            return 0;
        }

        $this->warn('Se encontraron ' . count($orphaned) . ' dominio(s) huérfano(s):');
        $this->newLine();

        $tableData = [];
        foreach ($orphaned as $domain) {
            $tableData[] = [
                $domain->id,
                $domain->domain,
                $domain->tenant_id,
                $domain->created_at,
            ];
        }

        $this->table(
            ['ID', 'Domain', 'Tenant ID', 'Creado'],
            $tableData
        );
        $this->newLine();

        if ($dryRun) {
            $this->info('Modo dry-run: No se eliminaron registros.');
            $this->info('Ejecute sin --dry-run para eliminar.');
            return 0;
        }

        // Confirm deletion
        if (!$this->confirm('¿Eliminar estos dominios huérfanos?', false)) {
            $this->info('Operación cancelada.');
            return 0;
        }

        // Delete orphaned domains
        $deleted = 0;
        foreach ($orphaned as $domain) {
            try {
                DB::connection('central')->table('domains')->where('id', $domain->id)->delete();
                $this->info("Eliminado: {$domain->domain} (ID: {$domain->id})");
                $deleted++;
            } catch (\Exception $e) {
                $this->error("Error al eliminar {$domain->domain}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Total eliminados: {$deleted}");
        
        return 0;
    }
}
