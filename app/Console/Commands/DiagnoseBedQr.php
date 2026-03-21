<?php

namespace App\Console\Commands;

use App\Models\Bed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class DiagnoseBedQr extends Command
{
    protected $signature = 'diagnose:bed-qr {token?}';
    protected $description = 'Diagnosticar problema de QR de camas - verificar ruta y token en BD';

    public function handle()
    {
        $token = $this->argument('token') ?? 'fDWcSRfYFpld9bC0jWJhAj6qIqzPujnW';

        $this->info('=== DIAGNÓSTICO QR CAMAS ===');
        $this->newLine();

        // 1. Verificar ruta
        $this->info('1. Verificando ruta qr.bed.show...');
        $routeExists = Route::has('qr.bed.show');
        
        if ($routeExists) {
            $route = Route::getRoutes()->getByName('qr.bed.show');
            $this->info('   ✓ Ruta existe');
            $this->line('   URI: ' . $route->uri());
            $this->line('   Action: ' . $route->getActionName());
        } else {
            $this->error('   ✗ Ruta NO existe');
            $this->warn('   Ejecutar: php artisan route:clear && php artisan optimize:clear');
            return 1;
        }

        $this->newLine();

        // 2. Verificar tenant
        $this->info('2. Verificando contexto tenant...');
        if (tenant()) {
            $this->info('   ✓ Tenant inicializado: ' . tenant()->id);
            $this->line('   DB: ' . config('database.default'));
        } else {
            $this->error('   ✗ Tenant NO inicializado');
            $this->warn('   Este comando debe ejecutarse en contexto tenant');
            $this->warn('   Usar: php artisan tenants:run diagnose:bed-qr --tenant=cuarta-temuco');
            return 1;
        }

        $this->newLine();

        // 3. Verificar token en BD
        $this->info('3. Verificando token en BD...');
        $this->line('   Token buscado: ' . $token);
        
        $bed = Bed::where('qr_token', $token)->first();
        
        if ($bed) {
            $this->info('   ✓ Token encontrado');
            $this->line('   ID: ' . $bed->id);
            $this->line('   Nombre: ' . $bed->name);
            $this->line('   Number: ' . ($bed->number ?? 'N/A'));
            $this->line('   QR Token: ' . $bed->qr_token);
        } else {
            $this->error('   ✗ Token NO encontrado en BD');
            
            $this->newLine();
            $this->warn('   Mostrando primeras 5 camas para comparación:');
            
            $beds = Bed::select('id', 'name', 'number', 'qr_token')
                ->limit(5)
                ->get();
            
            if ($beds->isEmpty()) {
                $this->warn('   No hay camas en la BD');
            } else {
                $this->table(
                    ['ID', 'Nombre', 'Number', 'QR Token'],
                    $beds->map(fn($b) => [
                        $b->id,
                        $b->name,
                        $b->number ?? 'N/A',
                        substr($b->qr_token ?? 'NULL', 0, 20) . '...'
                    ])
                );
            }
        }

        $this->newLine();

        // 4. Estadísticas
        $this->info('4. Estadísticas generales...');
        $totalBeds = Bed::count();
        $bedsWithToken = Bed::whereNotNull('qr_token')->count();
        
        $this->line('   Total camas: ' . $totalBeds);
        $this->line('   Camas con QR token: ' . $bedsWithToken);
        
        if ($totalBeds > 0 && $bedsWithToken === 0) {
            $this->error('   ¡PROBLEMA! Ninguna cama tiene qr_token generado');
            $this->warn('   Posible causa: migración no ejecutada o boot() del modelo no funcionó');
        }

        $this->newLine();

        // 5. Conclusión
        if ($routeExists && tenant() && $bed) {
            $this->info('=== DIAGNÓSTICO: TODO OK ===');
            $this->info('El QR debería funcionar correctamente');
            $this->line('URL: ' . route('qr.bed.show', $token));
            return 0;
        } else {
            $this->error('=== DIAGNÓSTICO: PROBLEMA DETECTADO ===');
            
            if (!$routeExists) {
                $this->warn('→ Ejecutar: php artisan route:clear && php artisan optimize:clear');
            }
            
            if (!$bed) {
                $this->warn('→ El token escaneado no existe en la BD del tenant');
                $this->warn('→ Verificar que el QR fue generado desde /admin/beds/{id}/qr de este tenant');
            }
            
            return 1;
        }
    }
}
