<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->bound('currentTenant') || ! tenant()) {
            $this->command?->warn('UserSeeder skipped — not in tenant context.');
            return;
        }

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrador',
                'email' => 'admin@' . tenant('id') . '.cl',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'active' => true,
            ]
        );
    }
}
