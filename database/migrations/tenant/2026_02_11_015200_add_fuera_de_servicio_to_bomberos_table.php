<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bomberos', function (Blueprint $table) {
            $table->boolean('fuera_de_servicio')->default(false)->after('es_sancion');
        });
    }

    public function down(): void
    {
        Schema::table('bomberos', function (Blueprint $table) {
            $table->dropColumn(['fuera_de_servicio']);
        });
    }
};
