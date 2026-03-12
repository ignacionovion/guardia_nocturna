<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Plan;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration:
     * 1. Adds the 'addons' JSON column to plans table
     * 2. Updates existing plans with correct features and addons from Plan::defaultPlans()
     */
    public function up(): void
    {
        // Step 1: Add addons column if it doesn't exist
        if (!Schema::connection('central')->hasColumn('plans', 'addons')) {
            Schema::connection('central')->table('plans', function (Blueprint $table) {
                $table->json('addons')->nullable()->after('features');
            });
        }

        // Step 2: Sync all plans with the correct features and addons
        $this->syncPlansWithDefaults();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('plans', function (Blueprint $table) {
            $table->dropColumn('addons');
        });
    }

    /**
     * Sync existing plans with the default features and addons from Plan.php
     */
    private function syncPlansWithDefaults(): void
    {
        $defaultPlans = Plan::defaultPlans();

        foreach ($defaultPlans as $planData) {
            $plan = Plan::where('slug', $planData['slug'])->first();

            if ($plan) {
                // Update existing plan with correct features and addons
                $plan->update([
                    'features' => $planData['features'],
                    'addons' => $planData['addons'],
                    'max_users' => $planData['max_users'],
                    'max_guardias' => $planData['max_guardias'],
                    'max_beds' => $planData['max_beds'],
                    'max_storage_mb' => $planData['max_storage_mb'],
                    'precio_mensual' => $planData['precio_mensual'],
                ]);
            } else {
                // Create new plan
                Plan::create($planData);
            }
        }
    }
};
