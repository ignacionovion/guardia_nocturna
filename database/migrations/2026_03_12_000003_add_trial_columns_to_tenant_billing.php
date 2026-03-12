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
                $table->index('trial_ends_at');
            });
        }

        // Actualizar enum estado_pago para incluir 'trial' (MariaDB/MySQL compatible)
        DB::statement("ALTER TABLE tenant_billing MODIFY COLUMN estado_pago ENUM('pagado', 'pendiente', 'vencido', 'suspendido', 'trial') DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        // Revertir enum a valores originales (sin 'trial')
        DB::statement("ALTER TABLE tenant_billing MODIFY COLUMN estado_pago ENUM('pagado', 'pendiente', 'vencido', 'suspendido') DEFAULT 'pendiente'");

        if (Schema::hasColumn('tenant_billing', 'trial_ends_at')) {
            Schema::table('tenant_billing', function (Blueprint $table) {
                $table->dropColumn('trial_ends_at');
            });
        }
    }
};
