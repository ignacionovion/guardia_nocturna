<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('firefighters', function (Blueprint $table) {
            $table->string('registration_number')->nullable()->after('email');
            $table->string('address_street')->nullable()->after('registration_number');
            $table->string('address_number')->nullable()->after('address_street');
        });
    }

    public function down(): void
    {
        Schema::table('firefighters', function (Blueprint $table) {
            $table->dropColumn(['registration_number', 'address_street', 'address_number']);
        });
    }
};
