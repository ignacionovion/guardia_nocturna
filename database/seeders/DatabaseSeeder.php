<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamar a los seeders
        $this->call([
            // SuperAdminSeeder::class,
            SystemSettingsSeeder::class,
            BedSeeder::class,
            CleaningTaskSeeder::class,
            GuardiaSeeder::class,
            GuardiaAssignmentsSnapshotSeeder::class,
            PlanillaListItemSeeder::class,
        ]);
    }
}
