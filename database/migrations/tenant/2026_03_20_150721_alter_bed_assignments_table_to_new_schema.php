<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bed_assignments', function (Blueprint $table) {
            // A) Agregar nuevas columnas si no existen
            if (!Schema::hasColumn('bed_assignments', 'started_at')) {
                $table->datetime('started_at')->nullable();
            }
            
            if (!Schema::hasColumn('bed_assignments', 'ended_at')) {
                $table->datetime('ended_at')->nullable();
            }
        });

        // B) Migrar datos antiguos
        // started_at = assigned_at donde started_at sea null
        if (Schema::hasColumn('bed_assignments', 'assigned_at')) {
            DB::table('bed_assignments')
                ->whereNull('started_at')
                ->update(['started_at' => DB::raw('assigned_at')]);
        }
        
        // ended_at = released_at donde ended_at sea null
        if (Schema::hasColumn('bed_assignments', 'released_at')) {
            DB::table('bed_assignments')
                ->whereNull('ended_at')
                ->update(['ended_at' => DB::raw('released_at')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bed_assignments', function (Blueprint $table) {
            // Eliminar columnas nuevas
            if (Schema::hasColumn('bed_assignments', 'ended_at')) {
                $table->dropColumn('ended_at');
            }
            
            if (Schema::hasColumn('bed_assignments', 'started_at')) {
                $table->dropColumn('started_at');
            }
        });
    }
};
