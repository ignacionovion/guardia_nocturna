<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->foreignId('bombero_id')->nullable()->after('creado_por')->constrained('bomberos')->nullOnDelete();

            $table->index(['bombero_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->dropIndex(['bombero_id', 'created_at']);
            $table->dropConstrainedForeignId('bombero_id');
        });
    }
};
