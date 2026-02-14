<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('preventive_shift_attendances');

        Schema::create('preventive_shift_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preventive_shift_assignment_id')
                ->constrained('preventive_shift_assignments', indexName: 'ps_att_psa_fk')
                ->cascadeOnDelete();
            $table->string('status', 32)->default('present');
            $table->timestamp('confirmed_at')->useCurrent();
            $table->string('confirm_ip', 64)->nullable();
            $table->string('confirm_user_agent')->nullable();
            $table->timestamps();

            $table->unique(['preventive_shift_assignment_id'], 'preventive_shift_attendances_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preventive_shift_attendances');
    }
};
