<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firefighters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardia_id')->nullable()->constrained('guardias')->nullOnDelete();

            $table->string('name');
            $table->string('last_name_paternal')->nullable();
            $table->string('last_name_maternal')->nullable();
            $table->string('rut')->nullable()->unique();

            $table->date('birthdate')->nullable();
            $table->date('admission_date')->nullable();

            $table->string('position_text')->nullable();
            $table->string('portable_number')->nullable();

            $table->boolean('is_driver')->default(false);
            $table->boolean('is_rescue_operator')->default(false);
            $table->boolean('is_trauma_assistant')->default(false);

            $table->boolean('is_shift_leader')->default(false);

            $table->enum('attendance_status', ['constituye', 'reemplazo', 'permiso', 'ausente', 'falta', 'licencia'])->default('constituye');
            $table->boolean('is_titular')->default(true);
            $table->boolean('is_exchange')->default(false);
            $table->boolean('is_penalty')->default(false);

            $table->timestamps();

            $table->index(['guardia_id', 'is_titular']);
            $table->index(['guardia_id', 'attendance_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firefighters');
    }
};
