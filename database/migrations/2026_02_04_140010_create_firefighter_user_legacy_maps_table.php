<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firefighter_user_legacy_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firefighter_id')->constrained('firefighters')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique('user_id');
            $table->unique('firefighter_id');
        });

        if (!Schema::hasColumn('users', 'role')) {
            return;
        }

        $now = now();

        DB::transaction(function () use ($now) {
            $users = DB::table('users')
                ->whereIn('role', ['bombero', 'jefe_guardia'])
                ->get();

            foreach ($users as $u) {
                $ffId = DB::table('firefighters')
                    ->where(function ($q) use ($u) {
                        if (!empty($u->rut)) {
                            $q->where('rut', $u->rut);
                            return;
                        }
                        $q->where('name', $u->name)
                          ->whereNull('rut');
                    })
                    ->orderBy('id')
                    ->value('id');

                if (!$ffId) {
                    continue;
                }

                DB::table('firefighter_user_legacy_maps')->updateOrInsert(
                    ['user_id' => $u->id],
                    [
                        'firefighter_id' => $ffId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firefighter_user_legacy_maps');
    }
};
