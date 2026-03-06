<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar permisos de rol por sección.
 * Se usa en rutas que requieren un permiso específico.
 */
class CheckRolePermission
{
    /**
     * Mapeo de secciones a rutas/rutas de referencia
     */
    private array $sectionMap = [
        'dashboard' => ['dashboard', '/'],
        'guardias' => ['guardias', 'admin.guardias', 'admin.guardia', 'guardia'],
        'dotaciones' => ['dotaciones', 'admin.dotaciones'],
        'calendario' => ['calendario', 'admin.calendar'],
        'voluntarios' => ['voluntarios', 'bomberos', 'admin.bomberos'],
        'usuarios' => ['usuarios', 'admin.users'],
        'roles' => ['roles', 'admin.roles'],
        'emergencias' => ['emergencias', 'admin.emergencies'],
        'reportes' => ['reportes', 'admin.reports'],
        'admin_system' => ['admin', 'system', 'settings', 'admin.settings'],
    ];

    public function handle(Request $request, Closure $next, ?string $requiredSection = null): Response
    {
        $user = $request->user();

        // Super admin y capitania tienen acceso a todo
        if ($user && in_array($user->role, ['super_admin', 'capitania'], true)) {
            return $next($request);
        }

        // Si no tiene rol asignado, denegar
        if (!$user || !$user->role_id) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        // Cargar permisos del rol
        $role = $user->role;
        if (!$role) {
            abort(403, 'Rol no válido.');
        }

        $permissions = $role->permissions ?? [];

        // Si se especificó una sección, verificar esa específica
        if ($requiredSection) {
            if (!in_array($requiredSection, $permissions, true)) {
                abort(403, 'No tienes permisos para acceder a esta sección.');
            }
            return $next($request);
        }

        // Detección automática por ruta actual
        $currentRoute = $request->route()?->getName() ?? '';
        $currentPath = $request->path();

        foreach ($this->sectionMap as $section => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($currentRoute, $pattern) || str_contains($currentPath, $pattern)) {
                    if (!in_array($section, $permissions, true)) {
                        abort(403, 'No tienes permisos para acceder a esta sección.');
                    }
                    return $next($request);
                }
            }
        }

        return $next($request);
    }
}
