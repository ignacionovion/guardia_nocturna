<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Modelo Plan - SaaS Multi-Tenant
 * 
 * Define los planes de suscripción y sus capacidades.
 * Soporta:
 * - Módulos del sistema (acceso a funcionalidades)
 * - Add-ons comerciales (features diferenciadores)
 * - Límites técnicos configurable por plan
 */
class Plan extends Model
{
    protected $connection = 'central';
    
    protected $fillable = [
        'slug',
        'nombre',
        'descripcion',
        'max_users',
        'max_guardias',
        'max_beds',
        'max_storage_mb',
        'max_emergencias_mes',
        'modules',
        'addons',
        'precio_mensual',
        'precio_anual',
        'trial_days',
        'visible_en_publico',
        'requiere_aprobacion',
        'orden',
        'activo',
    ];

    protected $casts = [
        'modules' => 'array',
        'addons' => 'array',
        'precio_mensual' => 'decimal:2',
        'precio_anual' => 'decimal:2',
        'visible_en_publico' => 'boolean',
        'requiere_aprobacion' => 'boolean',
        'activo' => 'boolean',
    ];

    /**
     * Módulos disponibles en el sistema
     * Estos son las funcionalidades principales de GuardiAPP
     */
    public static function availableModules(): array
    {
        return [
            'voluntarios' => [
                'label' => 'Gestión de Voluntarios/Bomberos',
                'icon' => 'users',
                'descripcion' => 'Registro y administración de bomberos y voluntarios',
                'requerido' => true, // Todos los planes deben tenerlo
            ],
            'emergencias' => [
                'label' => 'Módulo de Emergencias',
                'icon' => 'ambulance',
                'descripcion' => 'Gestión de llamados y emergencias',
            ],
            'dotaciones' => [
                'label' => 'Gestión de Dotaciones',
                'icon' => 'users-gear',
                'descripcion' => 'Organización de dotaciones y equipos',
            ],
            'calendario' => [
                'label' => 'Calendario de Guardias',
                'icon' => 'calendar-alt',
                'descripcion' => 'Planificación mensual/anual de guardias',
            ],
            'guardia' => [
                'label' => 'Sistema de Guardias',
                'icon' => 'shield',
                'descripcion' => 'Control de turnos y asistencia',
            ],
            'camas' => [
                'label' => 'Gestión de Camas',
                'icon' => 'bed',
                'descripcion' => 'Asignación de camas en cuartel',
            ],
            'reportes' => [
                'label' => 'Reportes y Estadísticas',
                'icon' => 'chart-bar',
                'descripcion' => 'Reportes básicos de actividad',
            ],
            'planilla' => [
                'label' => 'Planillas de Asistencia',
                'icon' => 'clipboard-list',
                'descripcion' => 'Registro histórico de asistencia',
            ],
            'now' => [
                'label' => 'Guardia NOW',
                'icon' => 'bolt',
                'descripcion' => 'Panel de guardia en tiempo real',
            ],
            'preventiva' => [
                'label' => 'Eventos Preventivos',
                'icon' => 'clipboard-check',
                'descripcion' => 'Gestión de eventos preventivos y acciones comunitarias',
            ],
            'inventario' => [
                'label' => 'Inventario de Equipamiento',
                'icon' => 'boxes',
                'descripcion' => 'Control de materiales y equipos',
            ],
        ];
    }

    /**
     * Add-ons comerciales disponibles
     * Estos son diferenciadores de plan, no funcionalidades base
     */
    public static function availableAddons(): array
    {
        return [
            'api_access' => [
                'label' => 'API REST',
                'icon' => 'code',
                'descripcion' => 'Acceso programático vía API REST',
                'precio_extra_mensual' => 0, // 0 = incluido en plan
            ],
            'webhooks' => [
                'label' => 'Webhooks',
                'icon' => 'webhook',
                'descripcion' => 'Notificaciones en tiempo real a URLs externas',
            ],
            'advanced_analytics' => [
                'label' => 'Analytics Avanzado',
                'icon' => 'chart-line',
                'descripcion' => 'Dashboards interactivos y predicciones con IA',
            ],
            'custom_branding' => [
                'label' => 'Personalización de Marca',
                'icon' => 'palette',
                'descripcion' => 'Logo, colores y dominio personalizado',
            ],
            'priority_support' => [
                'label' => 'Soporte Prioritario',
                'icon' => 'headset',
                'descripcion' => 'Atención 24/7 con tiempo de respuesta garantizado',
            ],
            'audit_logs' => [
                'label' => 'Logs de Auditoría',
                'icon' => 'history',
                'descripcion' => 'Registro detallado de todas las acciones',
            ],
            'sso' => [
                'label' => 'Single Sign-On',
                'icon' => 'key',
                'descripcion' => 'Integración con SAML, OAuth2, LDAP',
            ],
            'data_export' => [
                'label' => 'Exportación Avanzada',
                'icon' => 'file-export',
                'descripcion' => 'Exportar a Excel, PDF, CSV con filtros avanzados',
            ],
        ];
    }

    /**
     * Límites configurables por plan
     */
    public static function availableLimits(): array
    {
        return [
            'max_users' => [
                'label' => 'Usuarios máximos',
                'descripcion' => 'Cantidad de bomberos/voluntarios registrables',
                'unidad' => 'usuarios',
            ],
            'max_guardias' => [
                'label' => 'Guardias activas',
                'descripcion' => 'Número de guardias simultáneas permitidas',
                'unidad' => 'guardias',
            ],
            'max_beds' => [
                'label' => 'Camas disponibles',
                'descripcion' => 'Capacidad de gestión de camas en cuartel',
                'unidad' => 'camas',
            ],
            'max_storage_mb' => [
                'label' => 'Almacenamiento',
                'descripcion' => 'Espacio para documentos y fotos',
                'unidad' => 'MB',
            ],
            'max_emergencias_mes' => [
                'label' => 'Emergencias/mes',
                'descripcion' => 'Registro de emergencias (NULL = ilimitado)',
                'unidad' => 'emergencias',
            ],
        ];
    }

    /**
     * Planes por defecto para el SaaS
     */
    public static function defaultPlans(): array
    {
        return [
            [
                'slug' => 'basico',
                'nombre' => 'Básico',
                'descripcion' => 'Ideal para compañías pequeñas que están comenzando',
                'max_users' => 20,
                'max_guardias' => 3,
                'max_beds' => 10,
                'max_storage_mb' => 512,
                'max_emergencias_mes' => 50,
                'modules' => [
                    'voluntarios' => true,
                    'emergencias' => true,
                    'dotaciones' => true,
                    'calendario' => true,
                    'guardia' => true,
                    'camas' => true,
                    'reportes' => false,
                    'planilla' => false,
                    'now' => false,
                    'preventiva' => false,
                    'inventario' => false,
                ],
                'addons' => [
                    'api_access' => false,
                    'webhooks' => false,
                    'advanced_analytics' => false,
                    'custom_branding' => false,
                    'priority_support' => false,
                    'audit_logs' => false,
                    'sso' => false,
                    'data_export' => false,
                ],
                'precio_mensual' => 9990.00,
                'precio_anual' => 99900.00, // 2 meses gratis
                'trial_days' => 14,
                'visible_en_publico' => true,
                'requiere_aprobacion' => false,
                'orden' => 1,
                'activo' => true,
            ],
            [
                'slug' => 'profesional',
                'nombre' => 'Profesional',
                'descripcion' => 'Para compañías en crecimiento con necesidades avanzadas',
                'max_users' => 50,
                'max_guardias' => 10,
                'max_beds' => 30,
                'max_storage_mb' => 2048,
                'max_emergencias_mes' => null, // Ilimitado
                'modules' => [
                    'voluntarios' => true,
                    'emergencias' => true,
                    'dotaciones' => true,
                    'calendario' => true,
                    'guardia' => true,
                    'camas' => true,
                    'reportes' => true,
                    'planilla' => true,
                    'now' => false,
                    'preventiva' => false,
                    'inventario' => false,
                ],
                'addons' => [
                    'api_access' => false,
                    'webhooks' => true,
                    'advanced_analytics' => false,
                    'custom_branding' => false,
                    'priority_support' => false,
                    'audit_logs' => false,
                    'sso' => false,
                    'data_export' => true,
                ],
                'precio_mensual' => 19990.00,
                'precio_anual' => 199900.00,
                'trial_days' => 14,
                'visible_en_publico' => true,
                'requiere_aprobacion' => false,
                'orden' => 2,
                'activo' => true,
            ],
            [
                'slug' => 'enterprise',
                'nombre' => 'Enterprise',
                'descripcion' => 'Solución completa para grandes compañías y cuerpos de bomberos',
                'max_users' => null, // Ilimitado
                'max_guardias' => null,
                'max_beds' => null,
                'max_storage_mb' => 10240,
                'max_emergencias_mes' => null,
                'modules' => [
                    'voluntarios' => true,
                    'emergencias' => true,
                    'dotaciones' => true,
                    'calendario' => true,
                    'guardia' => true,
                    'camas' => true,
                    'reportes' => true,
                    'planilla' => true,
                    'now' => true,
                    'preventiva' => true,
                    'inventario' => true,
                ],
                'addons' => [
                    'api_access' => true,
                    'webhooks' => true,
                    'advanced_analytics' => true,
                    'custom_branding' => true,
                    'priority_support' => true,
                    'audit_logs' => true,
                    'sso' => true,
                    'data_export' => true,
                ],
                'precio_mensual' => 39990.00,
                'precio_anual' => 399900.00,
                'trial_days' => 30,
                'visible_en_publico' => true,
                'requiere_aprobacion' => true, // Enterprise requiere contacto comercial
                'orden' => 3,
                'activo' => true,
            ],
        ];
    }

    // ==================== MÉTODOS DE INSTANCIA ====================

    /**
     * Verificar si un módulo está habilitado en este plan
     */
    public function hasModule(string $module): bool
    {
        return $this->modules[$module] ?? false;
    }

    /**
     * Verificar si un add-on está habilitado en este plan
     */
    public function hasAddon(string $addon): bool
    {
        return $this->addons[$addon] ?? false;
    }

    /**
     * Obtener un límite específico
     */
    public function getLimit(string $type): ?int
    {
        return $this->$type ?? null;
    }

    /**
     * Verificar si hay límite para un recurso (NULL = ilimitado)
     */
    public function isUnlimited(string $type): bool
    {
        return $this->getLimit($type) === null;
    }

    // ==================== RELACIONES ====================

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    public function scopePublic($query)
    {
        return $query->where('visible_en_publico', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('orden');
    }

    // ==================== ACCESSORS ====================

    /**
     * Obtener lista de módulos habilitados
     */
    public function getEnabledModulesAttribute(): array
    {
        return array_keys(array_filter($this->modules ?? []));
    }

    /**
     * Obtener lista de add-ons habilitados
     */
    public function getEnabledAddonsAttribute(): array
    {
        return array_keys(array_filter($this->addons ?? []));
    }

    /**
     * Calcular precio con descuento anual
     */
    public function getDescuentoAnualAttribute(): ?float
    {
        if ($this->precio_mensual > 0 && $this->precio_anual > 0) {
            $anualSinDescuento = $this->precio_mensual * 12;
            return round((1 - $this->precio_anual / $anualSinDescuento) * 100, 1);
        }
        return null;
    }
}
