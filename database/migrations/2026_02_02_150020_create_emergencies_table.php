<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emergency_key_id')->constrained('emergency_keys')->restrictOnDelete();
            $table->dateTime('dispatched_at');
            $table->dateTime('arrived_at')->nullable();
            $table->text('details')->nullable();

            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('guardia_id')->nullable()->constrained('guardias')->nullOnDelete();
            $table->foreignId('officer_in_charge_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergencies');
    }
};
