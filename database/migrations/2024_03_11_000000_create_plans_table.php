<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de planes
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // basico, profesional, enterprise
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('max_users')->nullable(); // null = ilimitado
            $table->integer('max_guardias')->nullable();
            $table->integer('max_beds')->nullable();
            $table->integer('max_storage_mb')->nullable();
            $table->json('features'); // JSON con todas las features booleanas
            $table->decimal('precio_mensual', 10, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        // Agregar plan_id a tenants (referencia al plan)
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('plan')->constrained('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
        
        Schema::dropIfExists('plans');
    }
};
