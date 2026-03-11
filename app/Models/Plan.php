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
        'precio_mensual',
        'activo',
        'orden',
    ];

    protected $casts = [
        'features' => 'array',
        'activo' => 'boolean',
        'precio_mensual' => 'decimal:2',
    ];

    // Features disponibles en el sistema
    public static function availableFeatures(): array
    {
        return [
            'reportes_avanzados' => 'Reportes avanzados',
            'whatsapp_integration' => 'Integración WhatsApp',
            'estadisticas_avanzadas' => 'Estadísticas avanzadas',
            'backup_automatico' => 'Backup automático',
            'api_access' => 'Acceso API',
            'custom_branding' => 'Branding personalizado',
            'priority_support' => 'Soporte prioritario',
            'audit_logs' => 'Logs de auditoría',
            'multi_body' => 'Múltiples cuerpos',
            'advanced_notifications' => 'Notificaciones avanzadas',
        ];
    }

    // Planes por defecto
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
                    'reportes_avanzados' => false,
                    'whatsapp_integration' => false,
                    'estadisticas_avanzadas' => false,
                    'backup_automatico' => false,
                    'api_access' => false,
                    'custom_branding' => false,
                    'priority_support' => false,
                    'audit_logs' => false,
                    'multi_body' => false,
                    'advanced_notifications' => false,
                ],
                'precio_mensual' => 0,
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
                    'reportes_avanzados' => true,
                    'whatsapp_integration' => true,
                    'estadisticas_avanzadas' => true,
                    'backup_automatico' => true,
                    'api_access' => false,
                    'custom_branding' => false,
                    'priority_support' => false,
                    'audit_logs' => true,
                    'multi_body' => false,
                    'advanced_notifications' => true,
                ],
                'precio_mensual' => 29.99,
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
                    'reportes_avanzados' => true,
                    'whatsapp_integration' => true,
                    'estadisticas_avanzadas' => true,
                    'backup_automatico' => true,
                    'api_access' => true,
                    'custom_branding' => true,
                    'priority_support' => true,
                    'audit_logs' => true,
                    'multi_body' => true,
                    'advanced_notifications' => true,
                ],
                'precio_mensual' => 45.000,
                'activo' => true,
                'orden' => 3,
            ],
        ];
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    // Verificar si tiene una feature específica
    public function hasFeature(string $feature): bool
    {
        return $this->features[$feature] ?? false;
    }

    // Obtener límite (null = ilimitado)
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

    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('orden')->orderBy('id');
    }
}
