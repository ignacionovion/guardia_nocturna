<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turno_session_items', function (Blueprint $table) {
            $table->boolean('included')->default(true)->after('firefighter_id');
            $table->dateTime('removed_at')->nullable()->after('included');

            $table->index(['turno_session_id', 'included']);
            $table->index(['removed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('turno_session_items', function (Blueprint $table) {
            $table->dropIndex(['turno_session_id', 'included']);
            $table->dropIndex(['removed_at']);
            $table->dropColumn(['included', 'removed_at']);
        });
    }
};
