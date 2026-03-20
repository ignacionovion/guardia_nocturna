<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('beds', function (Blueprint $table) {
            // A) Agregar nuevas columnas si no existen
            if (!Schema::hasColumn('beds', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            
            if (!Schema::hasColumn('beds', 'location')) {
                $table->string('location')->nullable();
            }
            
            if (!Schema::hasColumn('beds', 'gender')) {
                $table->enum('gender', ['male', 'female', 'mixed'])->default('mixed');
            }
            
            if (!Schema::hasColumn('beds', 'notes')) {
                $table->text('notes')->nullable();
            }
            
            if (!Schema::hasColumn('beds', 'qr_token')) {
                $table->string('qr_token', 32)->nullable();
            }
            
            if (!Schema::hasColumn('beds', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
        });

        // B) Migrar datos antiguos
        DB::table('beds')->whereNull('name')->update([
            'name' => DB::raw('COALESCE(number, CONCAT("Cama ", id))')
        ]);
        
        DB::table('beds')->whereNull('notes')->update([
            'notes' => DB::raw('description')
        ]);

        // C) Generar qr_token único para registros que no lo tengan
        $bedsWithoutToken = DB::table('beds')->whereNull('qr_token')->get();
        foreach ($bedsWithoutToken as $bed) {
            $token = Str::random(32);
            // Asegurar que sea único
            while (DB::table('beds')->where('qr_token', $token)->exists()) {
                $token = Str::random(32);
            }
            DB::table('beds')->where('id', $bed->id)->update(['qr_token' => $token]);
        }

        // D) Crear índice único sobre qr_token si no existe
        if (!$this->indexExists('beds', 'qr_token')) {
            Schema::table('beds', function (Blueprint $table) {
                $table->unique('qr_token');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar índice único si existe
        if ($this->indexExists('beds', 'qr_token')) {
            Schema::table('beds', function (Blueprint $table) {
                $table->dropUnique(['qr_token']);
            });
        }
        
        Schema::table('beds', function (Blueprint $table) {
            // Eliminar columnas nuevas
            if (Schema::hasColumn('beds', 'created_by')) {
                $table->dropColumn('created_by');
            }
            
            if (Schema::hasColumn('beds', 'qr_token')) {
                $table->dropColumn('qr_token');
            }
            
            if (Schema::hasColumn('beds', 'notes')) {
                $table->dropColumn('notes');
            }
            
            if (Schema::hasColumn('beds', 'gender')) {
                $table->dropColumn('gender');
            }
            
            if (Schema::hasColumn('beds', 'location')) {
                $table->dropColumn('location');
            }
            
            if (Schema::hasColumn('beds', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    /**
     * Check if an index exists on a table using INFORMATION_SCHEMA.
     */
    private function indexExists(string $table, string $column): bool
    {
        $database = DB::getDatabaseName();
        
        $result = DB::select(
            "SELECT COUNT(*) as count 
             FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND column_name = ? 
             AND non_unique = 0",
            [$database, $table, $column]
        );
        
        return $result[0]->count > 0;
    }
};
