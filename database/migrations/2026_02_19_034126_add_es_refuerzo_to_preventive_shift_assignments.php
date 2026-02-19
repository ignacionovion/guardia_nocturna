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
        Schema::table('preventive_shift_assignments', function (Blueprint $table) {
            $table->boolean('es_refuerzo')->default(false)->after('bombero_id');
            $table->timestamp('entrada_hora')->nullable()->after('es_refuerzo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preventive_shift_assignments', function (Blueprint $table) {
            $table->dropColumn(['es_refuerzo', 'entrada_hora']);
        });
    }
};
