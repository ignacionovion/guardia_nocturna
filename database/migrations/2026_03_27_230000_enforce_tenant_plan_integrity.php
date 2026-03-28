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
$fallbackPlanId = DB::connection('central')
    ->table('plans')
    ->where('activo', 1)
    ->orderBy('id')
    ->value('id');
if (!$fallbackPlanId) {
    // No bloquear migración, solo loggear
    \Log::warning('No hay plan activo al ejecutar migración enforce_tenant_plan_integrity');
    return;
}

        // Backfill NULLs primero
DB::connection('central')
    ->table('tenants')
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

        Schema::table('tenants', function (Blueprint $table): void {
            $table->foreignId('plan_id')->nullable(false)->change();
        });

        Schema::table('tenants', function (Blueprint $table): void {
            $table->foreign('plan_id')->references('id')->on('plans')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropForeign(['plan_id']);
        });

        Schema::table('tenants', function (Blueprint $table): void {
            $table->foreignId('plan_id')->nullable()->change();
        });

        Schema::table('tenants', function (Blueprint $table): void {
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }
};
