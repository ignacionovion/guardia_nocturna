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
        Schema::create('guardia_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardia_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->foreignId('saved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('saved_at');
            $table->timestamps();

            $table->unique(['guardia_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardia_attendance_records');
    }
};
