<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bed_assignments', function (Blueprint $table) {
            $table->foreignId('firefighter_id')
                ->nullable()
                ->after('user_id')
                ->constrained('bomberos')
                ->nullOnDelete();
        });

        $mapTable = null;
        if (Schema::hasTable('mapa_bombero_usuario_legacy')) {
            $mapTable = 'mapa_bombero_usuario_legacy';
        } elseif (Schema::hasTable('firefighter_user_legacy_maps')) {
            $mapTable = 'firefighter_user_legacy_maps';
        }

        if ($mapTable) {
            DB::table('bed_assignments')
                ->whereNull('firefighter_id')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($mapTable) {
                    foreach ($rows as $row) {
                        $ffId = DB::table($mapTable)
                            ->where('user_id', (int) $row->user_id)
                            ->value('firefighter_id');

                        if ($ffId) {
                            DB::table('bed_assignments')->where('id', $row->id)->update(['firefighter_id' => $ffId]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::table('bed_assignments', function (Blueprint $table) {
            $table->dropForeign(['firefighter_id']);
            $table->dropColumn(['firefighter_id']);
        });
    }
};
