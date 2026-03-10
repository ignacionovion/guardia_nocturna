<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CentralAdmin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CentralAdminSeeder extends Seeder
{
    public function run(): void
    {
        CentralAdmin::updateOrCreate(
            ['email' => 'admin@guardianocturna.cl'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
