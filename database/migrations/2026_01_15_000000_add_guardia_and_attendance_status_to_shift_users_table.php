<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_users', function (Blueprint $table) {
            $table->foreignId('guardia_id')->nullable()->after('user_id')->constrained('guardias')->nullOnDelete();
            $table->string('attendance_status')->nullable()->after('present');

            $table->index(['guardia_id', 'attendance_status', 'start_time'], 'shift_users_guardia_status_start_idx');
        });
    }

    public function down(): void
    {
        Schema::table('shift_users', function (Blueprint $table) {
            $table->dropIndex('shift_users_guardia_status_start_idx');
            $table->dropForeign(['guardia_id']);
            $table->dropColumn(['guardia_id', 'attendance_status']);
        });
    }
};
