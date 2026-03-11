<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\Body;

class DiagnoseTenantIssuesCommand extends Command
{
    protected $signature = 'tenant:diagnose';
    protected $description = 'Diagnose tenant creation issues';

    public function handle(): int
    {
        $this->info('=== Diagnóstico de Problemas de Tenant ===');
        $this->newLine();

        // 1. Check database connection
        $this->info('1. Verificando conexión a base de datos...');
        try {
            $connection = DB::connection('central')->getPdo();
            $this->info('   ✓ Conexión exitosa');
            $this->info('   Driver: ' . $connection->getAttribute(\PDO::ATTR_DRIVER_NAME));
        } catch (\Exception $e) {
            $this->error('   ✗ Error de conexión: ' . $e->getMessage());
            return 1;
        }
        $this->newLine();

        // 2. Check if tenants table exists
        $this->info('2. Verificando tabla tenants...');
        if (DB::connection('central')->getSchemaBuilder()->hasTable('tenants')) {
            $this->info('   ✓ Tabla tenants existe');
            
            // Count tenants
            $count = Tenant::count();
            $this->info('   Total de tenants: ' . $count);
            
            // List all tenant IDs
            $ids = Tenant::pluck('id')->toArray();
            $this->info('   IDs existentes: ' . implode(', ', $ids));
            
            // Check for duplicates (shouldn't happen with primary key, but just in case)
            $duplicates = DB::connection('central')->select('
                SELECT id, COUNT(*) as count 
                FROM tenants 
                GROUP BY id 
                HAVING COUNT(*) > 1
            ');
            if (count($duplicates) > 0) {
                $this->error('   ✗ Se encontraron IDs duplicados:');
                foreach ($duplicates as $dup) {
                    $this->error('     - ' . $dup->id . ' (aparece ' . $dup->count . ' veces)');
                }
            } else {
                $this->info('   ✓ No hay IDs duplicados en tabla tenants');
            }
        } else {
            $this->error('   ✗ Tabla tenants no existe');
        }
        $this->newLine();

        // 3. Check domains table
        $this->info('3. Verificando tabla domains...');
        if (DB::connection('central')->getSchemaBuilder()->hasTable('domains')) {
            $this->info('   ✓ Tabla domains existe');
            
            $count = DB::connection('central')->table('domains')->count();
            $this->info('   Total de domains: ' . $count);
            
            // Check for orphaned domains (domains without tenants)
            $orphaned = DB::connection('central')->select('
                SELECT d.domain, d.tenant_id 
                FROM domains d 
                LEFT JOIN tenants t ON d.tenant_id = t.id 
                WHERE t.id IS NULL
            ');
            if (count($orphaned) > 0) {
                $this->warn('   ⚠ Se encontraron dominios huérfanos (sin tenant):');
                foreach ($orphaned as $orphan) {
                    $this->warn('     - domain: ' . $orphan->domain . ' (tenant_id: ' . $orphan->tenant_id . ')');
                }
                $this->warn('   Estos dominios pueden causar errores "duplicate entry" al crear nuevos tenants.');
            } else {
                $this->info('   ✓ No hay dominios huérfanos');
            }
            
            // Check for duplicate domains
            $dupDomains = DB::connection('central')->select('
                SELECT domain, COUNT(*) as count 
                FROM domains 
                GROUP BY domain 
                HAVING COUNT(*) > 1
            ');
            if (count($dupDomains) > 0) {
                $this->error('   ✗ Se encontraron domains duplicados:');
                foreach ($dupDomains as $dup) {
                    $this->error('     - ' . $dup->domain . ' (aparece ' . $dup->count . ' veces)');
                }
            } else {
                $this->info('   ✓ No hay domains duplicados');
            }
        } else {
            $this->error('   ✗ Tabla domains no existe');
        }
        $this->newLine();

        // 4. Check for companies table (in case validation is checking wrong table)
        $this->info('4. Verificando si existe tabla companies...');
        if (DB::connection('central')->getSchemaBuilder()->hasTable('companies')) {
            $this->warn('   ⚠ Tabla companies existe (podría causar confusión en validaciones)');
            $count = DB::connection('central')->table('companies')->count();
            $this->warn('   Registros en companies: ' . $count);
        } else {
            $this->info('   ✓ No existe tabla companies');
        }
        $this->newLine();

        // 5. Check tenant model configuration
        $this->info('5. Verificando configuración del modelo Tenant...');
        $tenant = new Tenant();
        $this->info('   Tabla: ' . $tenant->getTable());
        $this->info('   Conexión: ' . ($tenant->getConnectionName() ?? 'default'));
        $this->info('   Primary key: ' . $tenant->getKeyName());
        $this->newLine();

        // 6. Test validation rule
        $this->info('6. Probando regla de validación unique...');
        $testId = 'test-tenant-' . time();
        $exists = Tenant::where('id', $testId)->exists();
        $this->info('   ID de prueba: ' . $testId);
        $this->info('   Existe en DB: ' . ($exists ? 'Sí' : 'No'));
        $this->newLine();

        $this->info('=== Diagnóstico completado ===');
        
        return 0;
    }
}
