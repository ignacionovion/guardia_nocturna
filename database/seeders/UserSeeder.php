<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Credenciales de usuarios para tenants ya no se generan aquí en entornos productivos.
 * El acceso inicial del capitán se provisiona con TenantCaptainProvisioningService al crear
 * la compañía o mediante reset desde el panel SaaS.
 *
 * Si necesitas datos de demo en local, crea usuarios manualmente o usa un seeder dedicado
 * solo para desarrollo (no incluido en TenantDatabaseSeeder).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Intencionalmente vacío: evita contraseñas fijas en bases tenant.
    }
}
