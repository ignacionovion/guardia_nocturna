<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preventive_shift_assignments', function (Blueprint $table) {
            $table->foreignId('reemplaza_a_bombero_id')->nullable()->constrained('bomberos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('preventive_shift_assignments', function (Blueprint $table) {
            $table->dropForeign(['reemplaza_a_bombero_id']);
            $table->dropColumn('reemplaza_a_bombero_id');
        });
    }
};
