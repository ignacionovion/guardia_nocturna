<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        $users = DB::table('users')->select('id', 'email')->get();

        foreach ($users as $user) {
            $email = (string) ($user->email ?? '');
            $base = $email !== '' ? explode('@', $email)[0] : ('user' . $user->id);
            $candidate = strtolower(trim((string) $base));
            $candidate = preg_replace('/[^a-z0-9._-]/', '', $candidate) ?: ('user' . $user->id);

            $final = $candidate;
            $suffix = 1;
            while (DB::table('users')->where('username', $final)->exists()) {
                $suffix++;
                $final = $candidate . $suffix;
            }

            DB::table('users')->where('id', $user->id)->update(['username' => $final]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
