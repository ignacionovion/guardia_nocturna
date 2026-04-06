<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the tenant application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SystemSettingsSeeder::class,
            CleaningTaskSeeder::class,
            GuardiaSeeder::class,
            GuardiaAssignmentsSnapshotSeeder::class,
            PlanillaListItemSeeder::class,
        ]);
    }
}
