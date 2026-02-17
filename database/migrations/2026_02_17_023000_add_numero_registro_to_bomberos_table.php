<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bomberos', function (Blueprint $table) {
            if (!Schema::hasColumn('bomberos', 'numero_registro')) {
                $table->string('numero_registro', 255)->nullable()->after('correo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bomberos', function (Blueprint $table) {
            if (Schema::hasColumn('bomberos', 'numero_registro')) {
                $table->dropColumn('numero_registro');
            }
        });
    }
};
