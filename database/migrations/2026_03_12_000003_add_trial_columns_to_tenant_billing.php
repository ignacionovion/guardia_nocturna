<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if (!Schema::hasColumn('tenant_billing', 'trial_ends_at')) {
            Schema::table('tenant_billing', function (Blueprint $table): void {
                $table->timestamp('trial_ends_at')->nullable()->after('fecha_vencimiento');
            });
        }

        if (!Schema::hasColumn('tenant_billing', 'is_trial')) {
            Schema::table('tenant_billing', function (Blueprint $table): void {
                $table->boolean('is_trial')->default(false)->after('estado_pago');
            });
        }

        // SQLite no soporta MODIFY COLUMN con ENUM
        if ($driver === 'sqlite') {
            return;
        }

        DB::statement("
            ALTER TABLE tenant_billing
            MODIFY COLUMN estado_pago ENUM('pagado', 'pendiente', 'vencido', 'suspendido', 'trial')
            DEFAULT 'pendiente'
        ");
    }

    public function down(): void
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("
                ALTER TABLE tenant_billing
                MODIFY COLUMN estado_pago ENUM('pagado', 'pendiente', 'vencido', 'suspendido')
                DEFAULT 'pendiente'
            ");
        }

        if (Schema::hasColumn('tenant_billing', 'trial_ends_at')) {
            Schema::table('tenant_billing', function (Blueprint $table): void {
                $table->dropColumn('trial_ends_at');
            });
        }

        if (Schema::hasColumn('tenant_billing', 'is_trial')) {
            Schema::table('tenant_billing', function (Blueprint $table): void {
                $table->dropColumn('is_trial');
            });
        }
    }
};
