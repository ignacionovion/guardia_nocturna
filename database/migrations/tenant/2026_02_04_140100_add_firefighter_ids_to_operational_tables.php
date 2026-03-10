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
            $table->foreignId('firefighter_id')->nullable()->after('user_id')->constrained('firefighters')->nullOnDelete();
            $table->foreignId('replaced_firefighter_id')->nullable()->after('replaced_user_id')->constrained('firefighters')->nullOnDelete();
        });

        Schema::table('staff_events', function (Blueprint $table) {
            $table->foreignId('firefighter_id')->nullable()->after('user_id')->constrained('firefighters')->nullOnDelete();
            $table->foreignId('replacement_firefighter_id')->nullable()->after('replacement_user_id')->constrained('firefighters')->nullOnDelete();
        });

        Schema::table('cleaning_assignments', function (Blueprint $table) {
            $table->foreignId('firefighter_id')->nullable()->after('user_id')->constrained('firefighters')->nullOnDelete();
        });

        Schema::table('novelties', function (Blueprint $table) {
            $table->foreignId('firefighter_id')->nullable()->after('user_id')->constrained('firefighters')->nullOnDelete();
        });

        Schema::table('emergencies', function (Blueprint $table) {
            $table->foreignId('officer_in_charge_firefighter_id')->nullable()->after('officer_in_charge_user_id')->constrained('firefighters')->nullOnDelete();
        });

        $map = function (?int $userId) {
            if (!$userId) {
                return null;
            }

            return DB::table('firefighter_user_legacy_maps')
                ->where('user_id', $userId)
                ->value('firefighter_id');
        };

        DB::transaction(function () use ($map) {
            DB::table('shift_users')
                ->whereNull('firefighter_id')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($map) {
                    foreach ($rows as $row) {
                        $ffId = $map((int) $row->user_id);
                        if ($ffId) {
                            DB::table('shift_users')->where('id', $row->id)->update(['firefighter_id' => $ffId]);
                        }

                        if (!empty($row->replaced_user_id)) {
                            $repFfId = $map((int) $row->replaced_user_id);
                            if ($repFfId) {
                                DB::table('shift_users')->where('id', $row->id)->update(['replaced_firefighter_id' => $repFfId]);
                            }
                        }
                    }
                });

            DB::table('staff_events')
                ->whereNull('firefighter_id')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($map) {
                    foreach ($rows as $row) {
                        $ffId = $map((int) $row->user_id);
                        if ($ffId) {
                            DB::table('staff_events')->where('id', $row->id)->update(['firefighter_id' => $ffId]);
                        }

                        if (!empty($row->replacement_user_id)) {
                            $repFfId = $map((int) $row->replacement_user_id);
                            if ($repFfId) {
                                DB::table('staff_events')->where('id', $row->id)->update(['replacement_firefighter_id' => $repFfId]);
                            }
                        }
                    }
                });

            DB::table('cleaning_assignments')
                ->whereNull('firefighter_id')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($map) {
                    foreach ($rows as $row) {
                        $ffId = $map((int) $row->user_id);
                        if ($ffId) {
                            DB::table('cleaning_assignments')->where('id', $row->id)->update(['firefighter_id' => $ffId]);
                        }
                    }
                });

            DB::table('novelties')
                ->whereNull('firefighter_id')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($map) {
                    foreach ($rows as $row) {
                        $ffId = $map((int) $row->user_id);
                        if ($ffId) {
                            DB::table('novelties')->where('id', $row->id)->update(['firefighter_id' => $ffId]);
                        }
                    }
                });

            DB::table('emergencies')
                ->whereNull('officer_in_charge_firefighter_id')
                ->whereNotNull('officer_in_charge_user_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($map) {
                    foreach ($rows as $row) {
                        $ffId = $map((int) $row->officer_in_charge_user_id);
                        if ($ffId) {
                            DB::table('emergencies')->where('id', $row->id)->update(['officer_in_charge_firefighter_id' => $ffId]);
                        }
                    }
                });
        });
    }

    public function down(): void
    {
        Schema::table('emergencies', function (Blueprint $table) {
            $table->dropForeign(['officer_in_charge_firefighter_id']);
            $table->dropColumn(['officer_in_charge_firefighter_id']);
        });

        Schema::table('novelties', function (Blueprint $table) {
            $table->dropForeign(['firefighter_id']);
            $table->dropColumn(['firefighter_id']);
        });

        Schema::table('cleaning_assignments', function (Blueprint $table) {
            $table->dropForeign(['firefighter_id']);
            $table->dropColumn(['firefighter_id']);
        });

        Schema::table('staff_events', function (Blueprint $table) {
            $table->dropForeign(['firefighter_id']);
            $table->dropForeign(['replacement_firefighter_id']);
            $table->dropColumn(['firefighter_id', 'replacement_firefighter_id']);
        });

        Schema::table('shift_users', function (Blueprint $table) {
            $table->dropForeign(['firefighter_id']);
            $table->dropForeign(['replaced_firefighter_id']);
            $table->dropColumn(['firefighter_id', 'replaced_firefighter_id']);
        });
    }
};
