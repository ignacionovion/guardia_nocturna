<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('bombero')->after('email'); // super_admin, capitania, jefe_guardia, bombero
            $table->integer('age')->nullable()->after('role');
            $table->integer('years_of_service')->default(0)->after('age');
            $table->foreignId('guardia_id')->nullable()->constrained('guardias')->nullOnDelete()->after('years_of_service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['guardia_id']);
            $table->dropColumn(['role', 'age', 'years_of_service', 'guardia_id']);
        });
    }
};
