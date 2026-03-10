<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planilla_list_items', function (Blueprint $table) {
            $table->id();
            $table->string('unidad', 20);
            $table->string('section', 50);
            $table->string('item_key', 120);
            $table->string('label', 255);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['unidad', 'section', 'item_key'], 'planilla_list_items_unique');
            $table->index(['unidad', 'section', 'is_active'], 'planilla_list_items_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planilla_list_items');
    }
};
