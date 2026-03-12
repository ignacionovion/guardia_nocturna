<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'features',
        'addons',
        'precio_mensual',
        'activo',
        'orden',
    ];

    protected $casts = [
        'features' => 'array',
        'addons' => 'array',
        'activo' => 'boolean',
        'precio_mensual' => 'decimal:2',
    ];

    // ==================== MÓDULOS DEL SISTEMA ====================
    // Estos son los módulos operativos reales del sistema
    
    public static function availableModules(): array
    {
        return [
            'voluntarios' => 'Gestión de Voluntarios/Bomberos',
            'emergencias' => 'Módulo de Emergencias',
            'dotaciones' => 'Gestión de Dotaciones',
            'calendario' => 'Calendario y Planificación',
            'now' => 'Guardia NOW (tiempo real)',
            'guardia' => 'Gestión de Guardias',
            'camas' => 'Gestión de Camas',
            'reportes' => 'Reportes y Estadísticas',
            'planilla' => 'Planilla y Asistencia',
            'preventiva' => 'Mantenimiento Preventivo',
            'inventario' => 'Inventario de Materiales',
        ];
    }

    // Alias para compatibilidad
    public static function availableFeatures(): array
    {
        return self::availableModules();
    }

    // ==================== ADDONS SAAS ====================
    // Estos son features comerciales/extras del SaaS
    
    public static function availableAddons(): array
    {
        return [
            'api_access' => 'Acceso API REST',
            'custom_branding' => 'Marca Personalizada',
            'backup_automatico' => 'Backups Automáticos',
            'audit_logs' => 'Logs de Auditoría',
            'priority_support' => 'Soporte Prioritario',
            'webhooks' => 'Webhooks e Integraciones',
            'sso' => 'Single Sign-On (SSO)',
            'data_export' => 'Exportación Masiva de Datos',
        ];
    }

    // ==================== PLANES POR DEFECTO ====================
    
    public static function defaultPlans(): array
    {
        return [
            [
                'slug' => 'basico',
                'nombre' => 'Básico',
                'descripcion' => 'Plan básico para compañías pequeñas',
                'max_users' => 5,
                'max_guardias' => 20,
                'max_beds' => 10,
                'max_storage_mb' => 100,
                'features' => [
                    // Módulos del sistema
                    'voluntarios' => true,
                    'emergencias' => false,
                    'dotaciones' => true,
                    'calendario' => true,
                    'now' => false,
                    'guardia' => true,
                    'camas' => true,
                    'reportes' => false,
                    'planilla' => false,
                    'preventiva' => false,
                    'inventario' => false,
                ],
                'addons' => [
                    // Addons SaaS
                    'api_access' => false,
                    'custom_branding' => false,
                    'backup_automatico' => false,
                    'audit_logs' => false,
                    'priority_support' => false,
                    'webhooks' => false,
                    'sso' => false,
                    'data_export' => false,
                ],
                'precio_mensual' => 9990,
                'activo' => true,
                'orden' => 1,
            ],
            [
                'slug' => 'profesional',
                'nombre' => 'Profesional',
                'descripcion' => 'Plan profesional con más funcionalidades',
                'max_users' => 15,
                'max_guardias' => 50,
                'max_beds' => 30,
                'max_storage_mb' => 500,
                'features' => [
                    // Módulos del sistema
                    'voluntarios' => true,
                    'emergencias' => true,
                    'dotaciones' => true,
                    'calendario' => true,
                    'now' => false,
                    'guardia' => true,
                    'camas' => true,
                    'reportes' => true,
                    'planilla' => true,
                    'preventiva' => false,
                    'inventario' => false,
                ],
                'addons' => [
                    // Addons SaaS
                    'api_access' => false,
                    'custom_branding' => false,
                    'backup_automatico' => true,
                    'audit_logs' => false,
                    'priority_support' => false,
                    'webhooks' => false,
                    'sso' => false,
                    'data_export' => true,
                ],
                'precio_mensual' => 19990,
                'activo' => true,
                'orden' => 2,
            ],
            [
                'slug' => 'enterprise',
                'nombre' => 'Enterprise',
                'descripcion' => 'Plan enterprise con todas las funcionalidades',
                'max_users' => null, // ilimitado
                'max_guardias' => null,
                'max_beds' => null,
                'max_storage_mb' => 5000,
                'features' => [
                    // Módulos del sistema - TODOS
                    'voluntarios' => true,
                    'emergencias' => true,
                    'dotaciones' => true,
                    'calendario' => true,
                    'now' => true,
                    'guardia' => true,
                    'camas' => true,
                    'reportes' => true,
                    'planilla' => true,
                    'preventiva' => true,
                    'inventario' => true,
                ],
                'addons' => [
                    // Addons SaaS - TODOS
                    'api_access' => true,
                    'custom_branding' => true,
                    'backup_automatico' => true,
                    'audit_logs' => true,
                    'priority_support' => true,
                    'webhooks' => true,
                    'sso' => true,
                    'data_export' => true,
                ],
                'precio_mensual' => 39990,
                'activo' => true,
                'orden' => 3,
            ],
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    // ==================== VERIFICACIÓN DE FEATURES ====================

    /**
     * Verificar si tiene un módulo del sistema habilitado
     */
    public function hasModule(string $module): bool
    {
        return $this->features[$module] ?? false;
    }

    /**
     * Verificar si tiene un addon SaaS habilitado
     */
    public function hasAddon(string $addon): bool
    {
        $addons = $this->addons ?? [];
        return $addons[$addon] ?? false;
    }

    /**
     * Verificar si tiene una feature (módulo o addon)
     * Busca primero en módulos, luego en addons
     */
    public function hasFeature(string $feature): bool
    {
        // Primero buscar en módulos del sistema
        if (array_key_exists($feature, $this->features ?? [])) {
            return $this->features[$feature] ?? false;
        }
        
        // Luego buscar en addons
        if (array_key_exists($feature, $this->addons ?? [])) {
            return $this->addons[$feature] ?? false;
        }
        
        return false;
    }

    /**
     * Obtener todos los módulos habilitados
     */
    public function getEnabledModules(): array
    {
        return array_keys(array_filter($this->features ?? []));
    }

    /**
     * Obtener todos los addons habilitados
     */
    public function getEnabledAddons(): array
    {
        return array_keys(array_filter($this->addons ?? []));
    }

    /**
     * Obtener todas las features (módulos + addons) como array plano
     */
    public function getAllFeatures(): array
    {
        return array_merge(
            $this->features ?? [],
            $this->addons ?? []
        );
    }

    // ==================== LÍMITES ====================

    /**
     * Obtener límite (null = ilimitado)
     */
    public function getLimit(string $type): ?int
    {
        return match($type) {
            'users' => $this->max_users,
            'guardias' => $this->max_guardias,
            'beds' => $this->max_beds,
            'storage' => $this->max_storage_mb,
            default => null,
        };
    }

    /**
     * Verificar si un límite es ilimitado
     */
    public function isUnlimited(string $type): bool
    {
        return $this->getLimit($type) === null;
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('orden')->orderBy('id');
    }

    // ==================== HELPERS ESTÁTICOS ====================

    /**
     * Obtener features por defecto para un plan por slug
     */
    public static function getDefaultFeaturesForPlan(string $slug): array
    {
        $plans = collect(self::defaultPlans());
        $plan = $plans->firstWhere('slug', $slug);
        
        return $plan['features'] ?? self::getBasicFeatures();
    }

    /**
     * Obtener addons por defecto para un plan por slug
     */
    public static function getDefaultAddonsForPlan(string $slug): array
    {
        $plans = collect(self::defaultPlans());
        $plan = $plans->firstWhere('slug', $slug);
        
        return $plan['addons'] ?? self::getBasicAddons();
    }

    /**
     * Features mínimos (plan básico)
     */
    public static function getBasicFeatures(): array
    {
        return [
            'voluntarios' => true,
            'emergencias' => false,
            'dotaciones' => true,
            'calendario' => true,
            'now' => false,
            'guardia' => true,
            'camas' => true,
            'reportes' => false,
            'planilla' => false,
            'preventiva' => false,
            'inventario' => false,
        ];
    }

    /**
     * Addons mínimos (ninguno)
     */
    public static function getBasicAddons(): array
    {
        return [
            'api_access' => false,
            'custom_branding' => false,
            'backup_automatico' => false,
            'audit_logs' => false,
            'priority_support' => false,
            'webhooks' => false,
            'sso' => false,
            'data_export' => false,
        ];
    }
}
