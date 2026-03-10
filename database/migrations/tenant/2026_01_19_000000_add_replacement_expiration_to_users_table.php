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
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('replacement_until')->nullable()->index();
            $table->foreignId('original_guardia_id')->nullable()->constrained('guardias')->nullOnDelete();
            $table->string('original_attendance_status')->nullable();
            $table->boolean('original_is_titular')->nullable();
            $table->boolean('original_is_shift_leader')->nullable();
            $table->boolean('original_is_exchange')->nullable();
            $table->boolean('original_is_penalty')->nullable();
            $table->foreignId('original_job_replacement_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_role')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['original_guardia_id']);
            $table->dropForeign(['original_job_replacement_id']);
            $table->dropIndex(['replacement_until']);
            $table->dropColumn([
                'replacement_until',
                'original_guardia_id',
                'original_attendance_status',
                'original_is_titular',
                'original_is_shift_leader',
                'original_is_exchange',
                'original_is_penalty',
                'original_job_replacement_id',
                'original_role',
            ]);
        });
    }
};
