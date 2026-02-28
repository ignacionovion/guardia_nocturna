<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turno_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guardia_id');
            $table->unsignedBigInteger('shift_id')->nullable();

            // Fecha operativa del turno: corresponde al día en que inicia a las 22:00
            $table->date('operational_date');

            // Ventana del turno (zona horaria del sistema)
            $table->dateTime('opened_at');
            $table->dateTime('close_at');

            // Estados del draft
            $table->string('status', 20)->default('draft'); // draft|closed

            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('updated_by_user_id')->nullable();

            $table->timestamps();

            $table->index(['guardia_id', 'operational_date']);
            $table->unique(['guardia_id', 'operational_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turno_sessions');
    }
};
