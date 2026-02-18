<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GuardiaSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@guardianocturna.cl'],
            [
                'name' => 'Ignacio Novión',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'age' => 45,
                'years_of_service' => 20,
            ]
        );

        User::updateOrCreate(
            ['email' => 'capitan@guardianocturna.cl'],
            [
                'name' => 'Capitán',
                'username' => 'capitan',
                'password' => Hash::make('password'),
                'role' => 'capitania',
                'age' => 50,
                'years_of_service' => 25,
            ]
        );
    }
}
