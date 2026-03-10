<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bomberos', function (Blueprint $table) {
            if (!Schema::hasColumn('bomberos', 'es_permanente')) {
                $table->boolean('es_permanente')->default(false)->after('es_titular');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bomberos', function (Blueprint $table) {
            if (Schema::hasColumn('bomberos', 'es_permanente')) {
                $table->dropColumn('es_permanente');
            }
        });
    }
};
