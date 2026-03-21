<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('role', ['super_admin', 'capitania'])
            ->update(['role' => 'capitan']);
    }

    public function down(): void
    {
        // No reversible sin saber cuáles eran super_admin vs capitania
        // Se puede revertir manualmente si es necesario
    }
};
