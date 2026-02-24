<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardia_attendance_records', function (Blueprint $table) {
            $table->boolean('is_corrected')->default(false)->after('saved_at');
            $table->timestamp('corrected_at')->nullable()->after('is_corrected');
            $table->foreignId('corrected_by_user_id')->nullable()->constrained('users')->nullOnDelete()->after('corrected_at');
            $table->json('corrections_log')->nullable()->after('corrected_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('guardia_attendance_records', function (Blueprint $table) {
            $table->dropForeign(['corrected_by_user_id']);
            $table->dropColumn(['is_corrected', 'corrected_at', 'corrected_by_user_id', 'corrections_log']);
        });
    }
};
