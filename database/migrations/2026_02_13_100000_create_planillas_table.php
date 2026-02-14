<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planillas', function (Blueprint $table) {
            $table->id();
            $table->string('unidad', 20);
            $table->date('fecha_revision');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->json('data');
            $table->timestamps();

            $table->index(['unidad', 'fecha_revision'], 'planillas_unidad_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planillas');
    }
};
