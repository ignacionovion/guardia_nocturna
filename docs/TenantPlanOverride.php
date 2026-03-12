<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TenantPlanOverride - Personalizaciones por tenant
 * 
 * Permite:
 * - Habilitar módulos temporalmente (trial de feature)
 * - Ajustar límites para un tenant específico
 * - Promociones personalizadas
 */
class TenantPlanOverride extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'override_type',
        'override_key',
        'override_value',
        'valid_until',
        'created_by',
    ];

    protected $casts = [
        'override_value' => 'array',
        'valid_until' => 'datetime',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('valid_until')
                ->orWhere('valid_until', '>', now());
        });
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('override_type', $type);
    }

    // Relaciones
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // Métodos
    public function isExpired(): bool
    {
        return $this->valid_until !== null && $this->valid_until->isPast();
    }

    public function getValue(): mixed
    {
        return $this->override_value['value'] ?? null;
    }
}
