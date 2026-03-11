<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@' . tenant('id') . '.cl',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'active' => true,
        ]);
    }
}
