<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_users', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });

        if (Schema::hasTable('firefighter_user_legacy_maps')) {
            DB::transaction(function () {
                $rows = DB::table('shift_users')
                    ->whereNull('firefighter_id')
                    ->whereNotNull('user_id')
                    ->get(['id', 'user_id']);

                foreach ($rows as $row) {
                    $ffId = DB::table('firefighter_user_legacy_maps')->where('user_id', $row->user_id)->value('firefighter_id');
                    if ($ffId) {
                        DB::table('shift_users')->where('id', $row->id)->update(['firefighter_id' => $ffId]);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // Best-effort: only enforce NOT NULL if there are no nulls.
        $hasNulls = DB::table('shift_users')->whereNull('user_id')->exists();
        if ($hasNulls) {
            return;
        }

        Schema::table('shift_users', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
