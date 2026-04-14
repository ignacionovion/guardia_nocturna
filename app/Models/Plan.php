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
        'precio_anual',
        'activo',
        'orden',
    ];

    protected $casts = [
        'features' => 'array',
        'addons' => 'array',
        'activo' => 'boolean',
        'precio_mensual' => 'decimal:2',
        'precio_anual' => 'decimal:2',
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

    /**
     * Monto de facturación según ciclo: mensual → precio_mensual, anual → precio_anual.
     */
    public function montoSegunCiclo(string $billingCycle): float
    {
        $monthly = (float) ($this->precio_mensual ?? 0);

        if ($billingCycle === 'yearly') {
            $yearly = (float) ($this->precio_anual ?? 0);
            if ($yearly > 0) {
                return $yearly;
            }

            // Compatibilidad con catálogos legacy sin precio_anual.
            return $monthly * 12;
        }

        return $monthly;
    }

}
