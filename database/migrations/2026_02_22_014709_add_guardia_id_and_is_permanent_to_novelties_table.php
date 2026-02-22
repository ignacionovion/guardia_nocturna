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
            $table->foreignId('guardia_id')->nullable()->constrained('guardias')->onDelete('cascade');
            $table->boolean('is_permanent')->default(false);
            $table->foreignId('firefighter_id')->nullable()->after('user_id')->constrained('bomberos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('novelties', function (Blueprint $table) {
            $table->dropForeign(['guardia_id']);
            $table->dropForeign(['firefighter_id']);
            $table->dropColumn(['guardia_id', 'is_permanent', 'firefighter_id']);
        });
    }
};
