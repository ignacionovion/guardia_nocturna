<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardia_calendar_days', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->foreignId('guardia_id')->constrained('guardias')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['guardia_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardia_calendar_days');
    }
};
