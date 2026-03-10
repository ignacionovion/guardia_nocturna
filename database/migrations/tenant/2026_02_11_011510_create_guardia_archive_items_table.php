<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardia_archive_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardia_archive_id')->constrained('guardia_archives')->cascadeOnDelete();
            $table->foreignId('firefighter_id')->nullable()->constrained('bomberos')->nullOnDelete();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('payload');
            $table->timestamps();

            $table->index(['guardia_archive_id', 'entity_type']);
            $table->index(['firefighter_id', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardia_archive_items');
    }
};
