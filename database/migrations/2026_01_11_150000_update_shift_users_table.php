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
        Schema::table('shift_users', function (Blueprint $table) {
            // Eliminar columnas anteriores si es necesario o modificarlas
            // $table->dropColumn('role'); // Vamos a reutilizar o modificar 'role'

            // Asegurar que role pueda tener los nuevos valores
            // Agregar campos para control de asistencia y reemplazos
            $table->string('assignment_type')->nullable()->after('user_id'); // Para guardar "Oficial a cargo", "Bombero", etc.
            $table->foreignId('replaced_user_id')->nullable()->after('assignment_type')->constrained('users')->nullOnDelete();
            $table->timestamp('start_time')->nullable()->after('present');
            $table->timestamp('end_time')->nullable()->after('start_time');
            
            // Si 'present' era booleano, quizás necesitamos algo más complejo para "cumple falta"
            // O "cumple falta" es un assignment_type especial donde present = false?
            // El usuario lo puso en la lista de calidades. Lo manejaré en assignment_type.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_users', function (Blueprint $table) {
            $table->dropForeign(['replaced_user_id']);
            $table->dropColumn(['assignment_type', 'replaced_user_id', 'start_time', 'end_time']);
        });
    }
};
