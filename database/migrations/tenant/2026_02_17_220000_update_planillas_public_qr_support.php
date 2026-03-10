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
            if (!Schema::hasColumn('planillas', 'bombero_id')) {
                $table->foreignId('bombero_id')->nullable()->after('created_by')->constrained('bomberos')->nullOnDelete();
                $table->index(['bombero_id'], 'planillas_bombero_id_idx');
            }
        });

        if (Schema::hasColumn('planillas', 'created_by')) {
            $driver = DB::connection()->getDriverName();
            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE planillas MODIFY created_by BIGINT UNSIGNED NULL');
            }
        }
    }

    public function down(): void
    {
        Schema::table('planillas', function (Blueprint $table) {
            if (Schema::hasColumn('planillas', 'bombero_id')) {
                $table->dropIndex('planillas_bombero_id_idx');
                $table->dropConstrainedForeignId('bombero_id');
            }
        });

        Schema::table('planillas', function (Blueprint $table) {
            if (Schema::hasColumn('planillas', 'created_by')) {
                $driver = DB::connection()->getDriverName();
                if (in_array($driver, ['mysql', 'mariadb'], true)) {
                    DB::statement('ALTER TABLE planillas MODIFY created_by BIGINT UNSIGNED NOT NULL');
                }
            }
        });
    }
};
