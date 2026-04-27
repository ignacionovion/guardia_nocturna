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
            // UserSeeder / GuardiaSeeder: sin credenciales fijas; capitán vía TenantCaptainProvisioningService
            SystemSettingsSeeder::class,
            CleaningTaskSeeder::class,
            GuardiaAssignmentsSnapshotSeeder::class,
            PlanillaListItemSeeder::class,
            TenantSpecialtySeeder::class,
        ]);
    }
}
