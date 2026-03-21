<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardias', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('access_username')->nullable()->unique()->after('slug');
            $table->text('access_password_encrypted')->nullable()->after('access_username');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->after('access_password_encrypted');
        });
    }

    public function down(): void
    {
        Schema::table('guardias', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['slug', 'access_username', 'access_password_encrypted', 'user_id']);
        });
    }
};
