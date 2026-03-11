<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('estado', 20)->default('activo')->after('activo');
            $table->integer('grace_days')->default(5)->after('fecha_vencimiento');
        });

        // Migrate existing data: set estado based on activo field
        DB::table('tenants')->where('activo', true)->update(['estado' => 'activo']);
        DB::table('tenants')->where('activo', false)->update(['estado' => 'suspendido']);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['estado', 'grace_days']);
        });
    }
};
