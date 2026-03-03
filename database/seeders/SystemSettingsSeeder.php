<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'attendance_enable_time'            => '22:00',
            'attendance_disable_time'           => '07:00',
            'guardia_constitution_weekday_time' => '23:00',
            'guardia_constitution_sunday_time'  => '22:00',
            'guardia_daily_end_time'            => '07:00',
            'guardia_week_transition_time'      => '18:00',
            'guardia_week_cleanup_time'         => '18:00',
            'guardia_schedule_tz'               => 'America/Santiago',
        ];

        foreach ($defaults as $key => $value) {
            SystemSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
