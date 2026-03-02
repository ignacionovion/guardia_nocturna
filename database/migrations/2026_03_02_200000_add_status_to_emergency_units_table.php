<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emergency_units', function (Blueprint $table) {
            $table->string('status')->default('active')->after('description');
            $table->string('out_of_service_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('emergency_units', function (Blueprint $table) {
            $table->dropColumn(['status', 'out_of_service_reason']);
        });
    }
};
