<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardia_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardia_id')->constrained('guardias')->cascadeOnDelete();
            $table->dateTime('archived_at');
            $table->string('label')->nullable();
            $table->timestamps();

            $table->index(['guardia_id', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardia_archives');
    }
};
