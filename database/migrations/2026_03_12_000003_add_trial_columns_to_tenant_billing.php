<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar columna trial_ends_at si no existe
        if (!Schema::hasColumn('tenant_billing', 'trial_ends_at')) {
            Schema::table('tenant_billing', function (Blueprint $table) {
                $table->date('trial_ends_at')->nullable()->after('fecha_ultimo_pago');
            });
        }

        // Agregar índice para trial_ends_at si no existe
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listIndexes('tenant_billing');
        $hasTrialIndex = false;
        foreach ($indexes as $index) {
            if ($index->getName() === 'tenant_billing_trial_ends_at_index') {
                $hasTrialIndex = true;
                break;
            }
        }
        
        if (!$hasTrialIndex) {
            Schema::table('tenant_billing', function (Blueprint $table) {
                $table->index('trial_ends_at');
            });
        }

        // Actualizar enum estado_pago para incluir 'trial' (MariaDB/MySQL compatible)
        // Nota: ALTER ENUM no es soportado directamente en MySQL/MariaDB
        // Usamos MODIFY COLUMN para recrear el enum con los nuevos valores
        DB::statement("ALTER TABLE tenant_billing MODIFY COLUMN estado_pago ENUM('pagado', 'pendiente', 'vencido', 'suspendido', 'trial') DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        // Revertir enum a valores originales (sin 'trial')
        DB::statement("ALTER TABLE tenant_billing MODIFY COLUMN estado_pago ENUM('pagado', 'pendiente', 'vencido', 'suspendido') DEFAULT 'pendiente'");

        Schema::table('tenant_billing', function (Blueprint $table) {
            $table->dropIndex(['trial_ends_at']);
            $table->dropColumn('trial_ends_at');
        });
    }
};
