<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tenant_billing')) {
            return;
        }

        if (!Schema::hasColumn('tenant_billing', 'plan_id')) {
            Schema::table('tenant_billing', function (Blueprint $table): void {
                $table->unsignedBigInteger('plan_id')->nullable()->after('tenant_id');
            });
        }

        $fallbackPlanId = DB::table('plans')
            ->where('activo', true)
            ->orderBy('id')
            ->value('id');

        if (!$fallbackPlanId) {
            $fallbackPlanId = DB::table('plans')->orderBy('id')->value('id');
        }

        if (!$fallbackPlanId) {
            throw new RuntimeException('No hay planes disponibles para sincronizar tenant_billing.plan_id.');
        }

        $tenantPlanMap = DB::table('tenants')
            ->whereNotNull('plan_id')
            ->pluck('plan_id', 'id');

        $planSlugMap = DB::table('plans')
            ->pluck('id', 'slug');

        DB::table('tenant_billing')
            ->select('id', 'tenant_id', 'plan', 'plan_id')
            ->orderBy('id')
            ->get()
            ->each(function (object $billing) use ($tenantPlanMap, $planSlugMap, $fallbackPlanId): void {
                if (!empty($billing->plan_id)) {
                    return;
                }

                $resolvedPlanId = $tenantPlanMap[$billing->tenant_id] ?? null;

                if (!$resolvedPlanId && !empty($billing->plan)) {
                    $resolvedPlanId = $planSlugMap[$billing->plan] ?? null;
                }

                if (!$resolvedPlanId) {
                    $resolvedPlanId = $fallbackPlanId;
                }

                DB::table('tenant_billing')
                    ->where('id', $billing->id)
                    ->update(['plan_id' => $resolvedPlanId]);
            });

        try {
            Schema::table('tenant_billing', function (Blueprint $table): void {
                $table->index('plan_id', 'tenant_billing_plan_id_index');
            });
        } catch (Throwable $e) {
        }

        try {
            Schema::table('tenant_billing', function (Blueprint $table): void {
                $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
            });
        } catch (Throwable $e) {
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('tenant_billing') || !Schema::hasColumn('tenant_billing', 'plan_id')) {
            return;
        }

        try {
            Schema::table('tenant_billing', function (Blueprint $table): void {
                $table->dropForeign(['plan_id']);
            });
        } catch (Throwable $e) {
        }

        try {
            Schema::table('tenant_billing', function (Blueprint $table): void {
                $table->dropIndex('tenant_billing_plan_id_index');
            });
        } catch (Throwable $e) {
        }

        Schema::table('tenant_billing', function (Blueprint $table): void {
            $table->dropColumn('plan_id');
        });
    }
};
