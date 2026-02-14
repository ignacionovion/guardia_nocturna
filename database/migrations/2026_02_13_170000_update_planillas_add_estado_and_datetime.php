<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planillas', function (Blueprint $table) {
            if (!Schema::hasColumn('planillas', 'estado')) {
                $table->string('estado', 20)->default('en_edicion')->after('data');
                $table->index(['estado'], 'planillas_estado_idx');
            }
        });

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE planillas MODIFY fecha_revision DATETIME');
        } elseif ($driver === 'sqlite') {
            // SQLite requires table rebuild for type changes; keep as-is.
            // We will store full datetime strings; SQLite is permissive.
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE planillas MODIFY fecha_revision DATE');
        }

        Schema::table('planillas', function (Blueprint $table) {
            if (Schema::hasColumn('planillas', 'estado')) {
                $table->dropIndex('planillas_estado_idx');
                $table->dropColumn(['estado']);
            }
        });
    }
};
