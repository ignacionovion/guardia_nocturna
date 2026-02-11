<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bomberos', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('correo');
        });
    }

    public function down(): void
    {
        Schema::table('bomberos', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
