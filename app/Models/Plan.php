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
            'voluntarios' => 'Gestión de Voluntarios/Bomberos',
            'emergencias' => 'Módulo de Emergencias',
            'dotaciones' => 'Gestión de Dotaciones',
            'calendario' => 'Calendario y Planificación',
            'guardia' => 'Gestión de Guardias',
            'camas' => 'Gestión de Camas',
            'reportes' => 'Reportes y Estadísticas',
            'planilla' => 'Planilla y Asistencia',
            'now' => 'Guardia NOW (operativa)',
            'preventiva' => 'Mantenimiento Preventivo',
            'inventario' => 'Inventario de Materiales',
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
                    'voluntarios' => true,
                    'emergencias' => false,
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
