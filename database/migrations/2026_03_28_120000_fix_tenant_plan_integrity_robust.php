<?php

declare(strict_types=1);

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $fallbackPlanId = Plan::query()
            ->where('activo', true)
            ->orderBy('id')
            ->value('id');

if (!$fallbackPlanId) {
    \Log::warning('No hay plan activo al ejecutar fix_tenant_plan_integrity_robust');
    return;
}

        // Backfill NULLs primero
        DB::table('tenants')
            ->whereNull('plan_id')
            ->update(['plan_id' => $fallbackPlanId]);

        // Eliminar FK existente si hay
        try {
            Schema::table('tenants', function (Blueprint $table): void {
                $table->dropForeign(['plan_id']);
            });
        } catch (Throwable $e) {
            // FK no existe, continuar
        }

        // Crear nueva columna temporal NOT NULL
        Schema::table('tenants', function (Blueprint $table): void {
            $table->unsignedBigInteger('plan_id_new')->nullable();
        });

        // Migrar datos
        DB::table('tenants')->update([
            'plan_id_new' => DB::raw('plan_id')
        ]);

        // Hacer nueva columna NOT NULL
        Schema::table('tenants', function (Blueprint $table): void {
            $table->unsignedBigInteger('plan_id_new')->nullable(false)->change();
        });

        // Eliminar columna vieja y renombrar
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn('plan_id');
        });

        Schema::table('tenants', function (Blueprint $table): void {
            $table->renameColumn('plan_id_new', 'plan_id');
        });

        // Crear FK restrictiva
        Schema::table('tenants', function (Blueprint $table): void {
            $table->foreign('plan_id')->references('id')->on('plans')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropForeign(['plan_id']);
        });

        // Revertir a nullable
        Schema::table('tenants', function (Blueprint $table): void {
            $table->unsignedBigInteger('plan_id_new')->nullable();
        });

        DB::table('tenants')->update([
            'plan_id_new' => DB::raw('plan_id')
        ]);

        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropColumn('plan_id');
        });

        Schema::table('tenants', function (Blueprint $table): void {
            $table->renameColumn('plan_id_new', 'plan_id');
        });

        Schema::table('tenants', function (Blueprint $table): void {
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }
};
