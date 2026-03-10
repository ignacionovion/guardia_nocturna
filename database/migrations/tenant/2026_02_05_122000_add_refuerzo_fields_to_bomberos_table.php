<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bomberos', function (Blueprint $table) {
            $table->boolean('es_refuerzo')->default(false)->after('es_titular');
            $table->unsignedBigInteger('refuerzo_guardia_anterior_id')->nullable()->after('es_refuerzo');
        });
    }

    public function down(): void
    {
        Schema::table('bomberos', function (Blueprint $table) {
            $table->dropColumn(['es_refuerzo', 'refuerzo_guardia_anterior_id']);
        });
    }
};
