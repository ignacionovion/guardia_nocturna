<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('form_templates')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('data_json');
            $table->enum('estado', ['borrador', 'enviado'])->default('borrador');
            $table->timestamps();

            $table->index(['template_id', 'user_id']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
