<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preventive_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preventive_event_id')->constrained('preventive_events')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('preventive_shift_templates')->cascadeOnDelete();
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label')->nullable();
            $table->timestamps();

            $table->unique(['preventive_event_id', 'template_id', 'shift_date'], 'preventive_shifts_unique');
            $table->index(['preventive_event_id', 'shift_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_shifts');
    }
};
