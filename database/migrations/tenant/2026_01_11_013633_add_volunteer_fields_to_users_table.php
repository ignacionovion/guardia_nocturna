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
            $table->string('last_name_paternal')->nullable();
            $table->string('last_name_maternal')->nullable();
            $table->string('rut')->unique()->nullable();
            $table->string('company')->nullable(); // CIA
            $table->string('registration_number')->nullable(); // REGISTRO
            $table->string('company_registration_number')->nullable(); // REGISTRO CIA
            $table->string('call_code')->nullable(); // CODIGO DE LLAMADO
            $table->string('position_text')->nullable(); // CARGO (texto original del excel)
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->string('nationality')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('profession')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_complement')->nullable();
            $table->string('address_commune')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_name_paternal', 'last_name_maternal', 'rut', 'company',
                'registration_number', 'company_registration_number', 'call_code',
                'position_text', 'phone', 'gender', 'nationality', 'blood_group',
                'civil_status', 'profession', 'address_street', 'address_number',
                'address_complement', 'address_commune'
            ]);
        });
    }
};
