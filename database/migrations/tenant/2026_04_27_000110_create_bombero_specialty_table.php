<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bombero_specialty', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bombero_id')->constrained('bomberos')->cascadeOnDelete();
            $table->foreignId('specialty_id')->constrained('specialties')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['bombero_id', 'specialty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bombero_specialty');
    }
};
