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
            throw new RuntimeException('No existe ningún plan activo en la base de datos central. No se puede reforzar integridad de tenants.plan_id.');
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
