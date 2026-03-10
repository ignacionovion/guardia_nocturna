<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    public function up(): void
    {
        // Tabla de Cuerpos de Bomberos (opcional, body_id es nullable en tenants)
        Schema::create('bodies', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ciudad')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de Tenants = Compañías
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary(); // slug: "tercera-temuco"

            $table->foreignId('body_id')->nullable()->constrained('bodies')->nullOnDelete();
            $table->string('nombre');                          // "Tercera Compañía"
            $table->unsignedSmallInteger('numero')->nullable(); // 3
            $table->string('plan')->default('basico');          // basico | profesional | enterprise
            $table->boolean('activo')->default(true);
            $table->date('fecha_vencimiento')->nullable();

            $table->timestamps();
            $table->json('data')->nullable(); // requerido por stancl/tenancy
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('bodies');
    }
}
