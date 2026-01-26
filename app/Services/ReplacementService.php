<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ReplacementService
{
    public static function calculateReplacementUntil(Carbon $at): Carbon
    {
        $scheduleTz = env('GUARDIA_SCHEDULE_TZ', 'America/Santiago');
        $atLocal = $at->copy()->setTimezone($scheduleTz);

        $scheduleHourToday = $atLocal->isSunday() ? 22 : 23;
        $todayStart = $atLocal->copy()->startOfDay()->addHours($scheduleHourToday);

        if ($atLocal->greaterThanOrEqualTo($todayStart)) {
            $shiftStart = $todayStart;
        } else {
            if ($atLocal->hour < 7) {
                $yesterday = $atLocal->copy()->subDay();
                $scheduleHourYesterday = $yesterday->isSunday() ? 22 : 23;
                $shiftStart = $yesterday->copy()->startOfDay()->addHours($scheduleHourYesterday);
            } else {
                $shiftStart = $todayStart;
            }
        }

        $expiresAtLocal = $shiftStart->copy()->addDay()->startOfDay()->addHours(7);

        while ($expiresAtLocal->isWeekend()) {
            $expiresAtLocal->addDay();
        }

        return $expiresAtLocal->setTimezone(config('app.timezone'));
    }

    public static function expire(?Carbon $now = null): int
    {
        $now = $now ?: now();
        $processed = 0;

        if (!Schema::hasColumn('users', 'replacement_until')) {
            return 0;
        }

        User::whereNull('replacement_until')
            ->whereNotNull('job_replacement_id')
            ->where('attendance_status', 'reemplazo')
            ->where('is_titular', false)
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $user->update([
                        'replacement_until' => self::calculateReplacementUntil($user->updated_at ?? now()),
                    ]);
                }
            });

        User::whereNotNull('replacement_until')
            ->where('replacement_until', '<=', $now)
            ->chunkById(100, function ($users) use (&$processed) {
                foreach ($users as $user) {
                    $originalUserId = $user->job_replacement_id;

                    $user->update([
                        'guardia_id' => $user->original_guardia_id,
                        'attendance_status' => $user->original_attendance_status ?? 'constituye',
                        'is_titular' => $user->original_is_titular ?? false,
                        'is_shift_leader' => $user->original_is_shift_leader ?? false,
                        'is_exchange' => $user->original_is_exchange ?? false,
                        'is_penalty' => $user->original_is_penalty ?? false,
                        'job_replacement_id' => $user->original_job_replacement_id,
                        'role' => $user->original_role ?? $user->role,
                        'replacement_until' => null,
                        'original_guardia_id' => null,
                        'original_attendance_status' => null,
                        'original_is_titular' => null,
                        'original_is_shift_leader' => null,
                        'original_is_exchange' => null,
                        'original_is_penalty' => null,
                        'original_job_replacement_id' => null,
                        'original_role' => null,
                    ]);

                    if ($originalUserId) {
                        $originalUpdate = [
                            'attendance_status' => 'constituye',
                        ];

                        if (Schema::hasColumn('users', 'is_shift_leader')) {
                            $originalUpdate['is_shift_leader'] = false;
                        }
                        if (Schema::hasColumn('users', 'is_exchange')) {
                            $originalUpdate['is_exchange'] = false;
                        }
                        if (Schema::hasColumn('users', 'is_penalty')) {
                            $originalUpdate['is_penalty'] = false;
                        }

                        User::where('id', $originalUserId)->update($originalUpdate);
                    }

                    $processed++;
                }
            });

        return $processed;
    }
}
