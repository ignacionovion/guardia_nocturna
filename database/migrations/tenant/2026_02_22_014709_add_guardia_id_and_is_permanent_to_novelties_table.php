<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('novelties', function (Blueprint $table) {
            // Only add columns if they don't exist
            if (!Schema::hasColumn('novelties', 'guardia_id')) {
                $table->foreignId('guardia_id')->nullable()->constrained('guardias')->onDelete('cascade');
            }
            if (!Schema::hasColumn('novelties', 'is_permanent')) {
                $table->boolean('is_permanent')->default(false);
            }
            if (!Schema::hasColumn('novelties', 'firefighter_id')) {
                $table->foreignId('firefighter_id')->nullable()->after('user_id')->constrained('bomberos')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('novelties', function (Blueprint $table) {
            if (Schema::hasColumn('novelties', 'guardia_id')) {
                $table->dropForeign(['guardia_id']);
                $table->dropColumn('guardia_id');
            }
            if (Schema::hasColumn('novelties', 'firefighter_id')) {
                $table->dropForeign(['firefighter_id']);
                $table->dropColumn('firefighter_id');
            }
            if (Schema::hasColumn('novelties', 'is_permanent')) {
                $table->dropColumn('is_permanent');
            }
        });
    }
};
