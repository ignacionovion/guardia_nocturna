<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_shift_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preventive_event_id')->constrained('preventive_events')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('label')->nullable();
            $table->timestamps();

            $table->index(['preventive_event_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_shift_templates');
    }
};
