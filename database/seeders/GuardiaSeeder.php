<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Antes creaba usuarios demo con contraseña fija. Eso quedó fuera del flujo productivo.
 * El usuario inicial `capitan` lo gestiona TenantCaptainProvisioningService.
 */
class GuardiaSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Intencionalmente vacío.
    }
}
