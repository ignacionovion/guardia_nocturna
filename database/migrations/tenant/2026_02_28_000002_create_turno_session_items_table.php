<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turno_session_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('turno_session_id');
            $table->unsignedBigInteger('firefighter_id');

            $table->string('attendance_status', 30)->nullable();

            // Confirmación de concurrencia
            $table->string('confirm_token', 255)->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by_user_id')->nullable();

            // Cama asociada (solo si confirmado y en ventana)
            $table->unsignedBigInteger('bed_id')->nullable();

            $table->timestamps();

            $table->unique(['turno_session_id', 'firefighter_id']);
            $table->index(['firefighter_id']);
            $table->index(['confirmed_at']);

            $table->foreign('turno_session_id')->references('id')->on('turno_sessions')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turno_session_items');
    }
};
