<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración: Refactorizar tabla plans para arquitectura SaaS profesional
     * 
     * Estrategia:
     * 1. Renombrar features -> modules (mantener compatibilidad)
     * 2. Agregar columnas nuevas (addons, límites adicionales)
     * 3. Migrar datos legacy a nueva estructura
     * 4. Agregar tabla de overrides por tenant
     */
    public function up(): void
    {
        // Paso 1: Modificar tabla plans existente
        Schema::table('plans', function (Blueprint $table) {
            // Nuevos límites
            $table->integer('max_emergencias_mes')->unsigned()->nullable()->after('max_storage_mb');
            
            // Renombrar features a modules (mantener datos)
            // Nota: En MariaDB/MySQL no se puede renombrar directamente, usar alternativa
            
            // Nuevos campos de configuración
            $table->json('addons')->nullable()->after('features');
            $table->decimal('precio_anual', 10, 2)->default(0.00)->after('precio_mensual');
            $table->integer('trial_days')->unsigned()->default(14)->after('precio_anual');
            $table->boolean('visible_en_publico')->default(true)->after('trial_days');
            $table->boolean('requiere_aprobacion')->default(false)->after('visible_en_publico');
            $table->integer('orden')->unsigned()->default(0)->after('requiere_aprobacion');
        });

        // Paso 2: Migrar datos legacy
        $this->migrateLegacyData();

        // Paso 3: Crear tabla de overrides por tenant
        Schema::create('tenant_plan_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 255);
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->enum('override_type', ['module', 'addon', 'limit']);
            $table->string('override_key', 50);
            $table->json('override_value');
            $table->timestamp('valid_until')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'override_type', 'override_key']);
        });
    }

    /**
     * Migrar datos de features legacy a modules + addons
     */
    private function migrateLegacyData(): void
    {
        $plans = DB::table('plans')->get();

        foreach ($plans as $plan) {
            $legacyFeatures = json_decode($plan->features, true) ?? [];

            // Mapeo de features legacy a nueva estructura
            $moduleMapping = [
                // Legacy -> Nuevo
                'reportes_avanzados' => ['reportes' => true, 'planilla' => true],
                'whatsapp_integration' => [], // Era addon, ahora no usado
                'estadisticas_avanzadas' => ['reportes' => true],
                'backup_automatico' => [], // Addon ahora
                'api_access' => ['addons' => ['api_access' => true]],
                'custom_branding' => ['addons' => ['custom_branding' => true]],
                'priority_support' => ['addons' => ['priority_support' => true]],
                'audit_logs' => ['addons' => ['audit_logs' => true]],
                'multi_body' => [], // No implementado aún
                'advanced_notifications' => [], // Era addon
            ];

            $newModules = [];
            $newAddons = [];

            // Defaults según el slug del plan
            switch ($plan->slug) {
                case 'basico':
                    $newModules = [
                        'voluntarios' => true,
                        'emergencias' => true,
                        'dotaciones' => true,
                        'calendario' => true,
                        'guardia' => true,
                        'camas' => true,
                        'reportes' => false,
                        'planilla' => false,
                        'now' => false,
                        'preventiva' => false,
                        'inventario' => false,
                    ];
                    break;
                case 'profesional':
                    $newModules = [
                        'voluntarios' => true,
                        'emergencias' => true,
                        'dotaciones' => true,
                        'calendario' => true,
                        'guardia' => true,
                        'camas' => true,
                        'reportes' => true,
                        'planilla' => true,
                        'now' => false,
                        'preventiva' => false,
                        'inventario' => false,
                    ];
                    $newAddons = ['data_export' => true, 'webhooks' => true];
                    break;
                case 'enterprise':
                    $newModules = [
                        'voluntarios' => true,
                        'emergencias' => true,
                        'dotaciones' => true,
                        'calendario' => true,
                        'guardia' => true,
                        'camas' => true,
                        'reportes' => true,
                        'planilla' => true,
                        'now' => true,
                        'preventiva' => true,
                        'inventario' => true,
                    ];
                    $newAddons = [
                        'api_access' => true,
                        'webhooks' => true,
                        'advanced_analytics' => true,
                        'custom_branding' => true,
                        'priority_support' => true,
                        'audit_logs' => true,
                        'sso' => true,
                        'data_export' => true,
                    ];
                    break;
            }

            // Aplicar mapeo de features legacy
            foreach ($legacyFeatures as $feature => $enabled) {
                if ($enabled && isset($moduleMapping[$feature])) {
                    $mapping = $moduleMapping[$feature];
                    if (isset($mapping['addons'])) {
                        $newAddons = array_merge($newAddons, $mapping['addons']);
                    } else {
                        $newModules = array_merge($newModules, $mapping);
                    }
                }
            }

            // Actualizar registro
            DB::table('plans')
                ->where('id', $plan->id)
                ->update([
                    'features' => json_encode($newModules), // Mantiene columna existente
                    'addons' => json_encode($newAddons),
                    'precio_anual' => $plan->precio_mensual * 10, // 2 meses gratis
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_plan_overrides');
        
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn([
                'max_emergencias_mes',
                'addons',
                'precio_anual',
                'trial_days',
                'visible_en_publico',
                'requiere_aprobacion',
                'orden',
            ]);
        });
    }
};
