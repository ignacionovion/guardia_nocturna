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
            $table->boolean('is_shift_leader')->default(false);
            $table->boolean('is_exchange')->default(false);
            $table->boolean('is_penalty')->default(false);
        });

        // Migrar datos existentes de job_type a las nuevas columnas
        $users = User::whereNotNull('job_type')->get();
        foreach ($users as $user) {
            $updates = [];
            switch ($user->job_type) {
                case 'Oficial a cargo':
                    $updates['is_shift_leader'] = true;
                    break;
                case 'Canje':
                    $updates['is_exchange'] = true;
                    break;
                case 'Cumple falta':
                    $updates['is_penalty'] = true;
                    break;
            }
            if (!empty($updates)) {
                $user->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_shift_leader', 'is_exchange', 'is_penalty']);
        });
    }
};
