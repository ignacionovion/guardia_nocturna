<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_rescue_operator')->default(false);
            $table->boolean('is_trauma_assistant')->default(false);
        });

        // Migrar datos existentes
        $users = User::whereIn('job_type', ['Asistente trauma', 'Operador de rescate'])->get();
        foreach ($users as $user) {
            if ($user->job_type === 'Asistente trauma') {
                $user->update([
                    'is_trauma_assistant' => true,
                    'job_type' => 'Bombero' // Resetear a Bombero por defecto
                ]);
            } elseif ($user->job_type === 'Operador de rescate') {
                $user->update([
                    'is_rescue_operator' => true,
                    'job_type' => 'Bombero'
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_rescue_operator', 'is_trauma_assistant']);
        });
    }
};
