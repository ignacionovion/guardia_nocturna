<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('central')->hasTable('central_admins')) {
            return;
        }

        $driver = Schema::connection('central')->getConnection()->getDriverName();

        Schema::connection('central')->table('central_admins', function (Blueprint $table) {
            if (! Schema::connection('central')->hasColumn('central_admins', 'activo')) {
                $table->boolean('activo')->default(true)->after('remember_token');
            }
            if (! Schema::connection('central')->hasColumn('central_admins', 'is_super_admin')) {
                $table->boolean('is_super_admin')->default(false)->after('activo');
            }
        });

        if ($driver === 'mysql') {
            try {
                DB::connection('central')->statement('ALTER TABLE central_admins DROP INDEX central_admins_email_unique');
            } catch (\Throwable) {
                // índice con otro nombre o ya eliminado
            }
            try {
                DB::connection('central')->statement('ALTER TABLE central_admins MODIFY email VARCHAR(255) NULL');
            } catch (\Throwable) {
                //
            }
        }

        $firstId = DB::connection('central')->table('central_admins')->orderBy('id')->value('id');
        if ($firstId !== null) {
            DB::connection('central')->table('central_admins')->where('id', $firstId)->update([
                'is_super_admin' => true,
                'activo' => true,
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::connection('central')->hasTable('central_admins')) {
            return;
        }

        Schema::connection('central')->table('central_admins', function (Blueprint $table) {
            if (Schema::connection('central')->hasColumn('central_admins', 'is_super_admin')) {
                $table->dropColumn('is_super_admin');
            }
            if (Schema::connection('central')->hasColumn('central_admins', 'activo')) {
                $table->dropColumn('activo');
            }
        });
    }
};
