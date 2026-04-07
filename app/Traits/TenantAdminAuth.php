<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * Trait para centralizar autorización de módulos tenant-administrativos.
 * 
 * Permite acceso a roles que funcionalmente administran un tenant:
 * - CAPITAN (rol principal de administración tenant)
 * - super_admin (rol central, compatibilidad)
 * - capitania (variante legacy, compatibilidad)
 */
trait TenantAdminAuth
{
    /**
     * Verifica si el usuario actual tiene permisos de administrador de tenant.
     * 
     * @return bool
     */
    protected function isTenantAdmin(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Roles con acceso a módulos tenant-administrativos
        return in_array($user->role, [
            'capitan',      // Rol principal de administración tenant
            'super_admin',  // Rol central (compatibilidad)
            'capitania',    // Variante legacy (compatibilidad)
        ], true);
    }

    /**
     * Aborta con 403 si el usuario no es administrador de tenant.
     * 
     * @param string $message
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function requireTenantAdmin(string $message = 'No autorizado.'): void
    {
        if (!$this->isTenantAdmin()) {
            abort(403, $message);
        }
    }

    /**
     * Verifica si el usuario actual tiene permisos de administrador avanzado.
     * Incluye roles legacy para compatibilidad en módulos específicos.
     * 
     * @return bool
     */
    protected function isAdvancedAdmin(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Incluir 'admin' para módulos que históricamente lo permitieron
        return in_array($user->role, [
            'capitan',
            'super_admin', 
            'capitania',
            'admin',       // Legacy compatibility
        ], true);
    }

    /**
     * Aborta con 403 si el usuario no es administrador avanzado.
     * 
     * @param string $message
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function requireAdvancedAdmin(string $message = 'No autorizado.'): void
    {
        if (!$this->isAdvancedAdmin()) {
            abort(403, $message);
        }
    }
}
