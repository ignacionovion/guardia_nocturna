<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Guardia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class GuardiaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@guardianocturna.cl',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'age' => 45,
            'years_of_service' => 20,
        ]);

        // 2. Capitania
        User::create([
            'name' => 'Capitán General',
            'email' => 'capitan@guardianocturna.cl',
            'password' => Hash::make('password'),
            'role' => 'capitania',
            'age' => 50,
            'years_of_service' => 25,
        ]);

        // Guardias y su personal
        $guardiasData = [
            'Blitzkrieg' => [
                'jefe' => ['name' => 'Comandante Blitz', 'age' => 40, 'years' => 15],
                'bomberos' => [
                    ['name' => 'Jürgen Klopp', 'age' => 38, 'years' => 12],
                    ['name' => 'Thomas Müller', 'age' => 35, 'years' => 10],
                    ['name' => 'Manuel Neuer', 'age' => 33, 'years' => 8],
                    ['name' => 'Philipp Lahm', 'age' => 30, 'years' => 6],
                    ['name' => 'Bastian Schweinsteiger', 'age' => 28, 'years' => 4],
                    ['name' => 'Toni Kroos', 'age' => 25, 'years' => 2],
                ]
            ],
            'Batallion 32' => [
                'jefe' => ['name' => 'Sargento Gordon', 'age' => 42, 'years' => 18],
                'bomberos' => [
                    ['name' => 'Bruce Wayne', 'age' => 35, 'years' => 12],
                    ['name' => 'Dick Grayson', 'age' => 28, 'years' => 9],
                    ['name' => 'Jason Todd', 'age' => 26, 'years' => 7],
                    ['name' => 'Tim Drake', 'age' => 24, 'years' => 5],
                    ['name' => 'Barbara Gordon', 'age' => 25, 'years' => 4],
                    ['name' => 'Damian Wayne', 'age' => 20, 'years' => 1],
                ]
            ],
            'Feuerwaffen' => [
                'jefe' => ['name' => 'Teniente Landa', 'age' => 45, 'years' => 20],
                'bomberos' => [
                    ['name' => 'Aldo Raine', 'age' => 38, 'years' => 13],
                    ['name' => 'Shosanna Dreyfus', 'age' => 30, 'years' => 8],
                    ['name' => 'Bridget von Hammersmark', 'age' => 32, 'years' => 7],
                    ['name' => 'Archie Hicox', 'age' => 29, 'years' => 5],
                    ['name' => 'Hugo Stiglitz', 'age' => 35, 'years' => 4],
                    ['name' => 'Donny Donowitz', 'age' => 28, 'years' => 3],
                ]
            ],
        ];

        foreach ($guardiasData as $guardiaName => $data) {
            $guardia = Guardia::create(['name' => $guardiaName]);

            // Crear Usuario "Institucional" para la Guardia (Login de Guardia)
            User::create([
                'name' => $guardiaName, // El nombre de la guardia es el nombre de usuario
                'email' => strtolower(str_replace(' ', '.', $guardiaName)) . '@guardianocturna.cl',
                'password' => Hash::make('password'),
                'role' => 'guardia', // Nuevo rol específico para cuentas de guardia
                'guardia_id' => $guardia->id,
                'years_of_service' => 0,
                'age' => 0,
            ]);

            // Jefe de Guardia
            User::create([
                'name' => $data['jefe']['name'],
                'email' => strtolower(str_replace(' ', '.', $data['jefe']['name'])) . '@guardianocturna.cl',
                'password' => Hash::make('password'),
                'role' => 'jefe_guardia',
                'age' => $data['jefe']['age'],
                'years_of_service' => $data['jefe']['years'],
                'guardia_id' => $guardia->id,
            ]);

            // Bomberos
            foreach ($data['bomberos'] as $bombero) {
                User::create([
                    'name' => $bombero['name'],
                    'email' => strtolower(str_replace(' ', '.', $bombero['name'])) . '@guardianocturna.cl',
                    'password' => Hash::make('password'),
                    'role' => 'bombero',
                    'age' => $bombero['age'],
                    'years_of_service' => $bombero['years'],
                    'guardia_id' => $guardia->id,
                ]);
            }
        }
    }
}
