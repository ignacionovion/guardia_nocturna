<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_billing', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly')->after('monto');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_billing', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
        });
    }
};
