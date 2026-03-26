<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CentralAdmin;
use Illuminate\Support\Facades\Hash;

class CentralAdminSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('CENTRAL_ADMIN_USERNAME', 'admin');
        $email = env('CENTRAL_ADMIN_EMAIL');
        $password = env('CENTRAL_ADMIN_PASSWORD');

        if (!$email || !$password) {
            $this->command->error('Faltan variables CENTRAL_ADMIN en .env');
            return;
        }

        CentralAdmin::updateOrCreate(
            ['username' => $username],
            [
                'name' => 'Administrador',
                'email' => $email,
                'password' => Hash::make($password),
            ]
        );

        $this->command->info('Admin central verificado/creado correctamente');
    }
}
