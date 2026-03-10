<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_shift_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preventive_shift_id')->constrained('preventive_shifts')->cascadeOnDelete();
            $table->foreignId('bombero_id')->constrained('bomberos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['preventive_shift_id', 'bombero_id'], 'preventive_shift_assignments_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_shift_assignments');
    }
};
