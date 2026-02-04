<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('firefighters', function (Blueprint $table) {
            $table->string('email')->nullable()->after('rut');
        });

        if (Schema::hasColumn('users', 'role') && Schema::hasColumn('users', 'email')) {
            DB::transaction(function () {
                $users = DB::table('users')
                    ->whereIn('role', ['bombero', 'jefe_guardia'])
                    ->get(['id', 'email']);

                foreach ($users as $u) {
                    $ffId = DB::table('firefighter_user_legacy_maps')->where('user_id', $u->id)->value('firefighter_id');
                    if (!$ffId) continue;

                    if (!$u->email) continue;
                    if (str_contains($u->email, '@system.local')) continue;

                    DB::table('firefighters')->where('id', $ffId)->update(['email' => $u->email]);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('firefighters', function (Blueprint $table) {
            $table->dropColumn(['email']);
        });
    }
};
