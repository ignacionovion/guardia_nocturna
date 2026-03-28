<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add addons column if it doesn't exist
        if (!Schema::connection('central')->hasColumn('plans', 'addons')) {
            Schema::connection('central')->table('plans', function (Blueprint $table) {
                $table->json('addons')->nullable()->after('features');
            });
        }

        DB::connection('central')->table('plans')
            ->whereNull('addons')
            ->update(['addons' => json_encode([])]);
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

};
