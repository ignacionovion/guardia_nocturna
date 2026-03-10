<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bed_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('bed_assignments', 'assigned_source')) {
                $table->string('assigned_source', 50)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('bed_assignments', 'assigned_ip')) {
                $table->string('assigned_ip', 64)->nullable()->after('assigned_source');
            }
            if (!Schema::hasColumn('bed_assignments', 'assigned_user_agent')) {
                $table->string('assigned_user_agent', 500)->nullable()->after('assigned_ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bed_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('bed_assignments', 'assigned_user_agent')) {
                $table->dropColumn('assigned_user_agent');
            }
            if (Schema::hasColumn('bed_assignments', 'assigned_ip')) {
                $table->dropColumn('assigned_ip');
            }
            if (Schema::hasColumn('bed_assignments', 'assigned_source')) {
                $table->dropColumn('assigned_source');
            }
        });
    }
};
