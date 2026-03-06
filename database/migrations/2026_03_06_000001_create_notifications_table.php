<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index(); // attendance_saved, replacement, novelty, bed_assigned, etc.
            $table->string('title', 255);
            $table->text('message')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // User who triggered
            $table->foreignId('firefighter_id')->nullable()->constrained('bomberos')->nullOnDelete();
            $table->foreignId('guardia_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata')->nullable(); // Additional context data
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
            $table->index(['read_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
