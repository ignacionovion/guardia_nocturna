<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firefighter_replacements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guardia_id')->nullable()->constrained('guardias')->nullOnDelete();

            $table->foreignId('original_firefighter_id')->constrained('firefighters')->onDelete('cascade');
            $table->foreignId('replacement_firefighter_id')->constrained('firefighters')->onDelete('cascade');

            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();

            $table->enum('status', ['active', 'closed'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['original_firefighter_id', 'status']);
            $table->index(['replacement_firefighter_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firefighter_replacements');
    }
};
