<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_emergency_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_id')->constrained('emergencies')->cascadeOnDelete();
            $table->foreignId('emergency_unit_id')->constrained('emergency_units')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['emergency_id', 'emergency_unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_emergency_unit');
    }
};
