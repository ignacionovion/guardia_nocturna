<?php

declare(strict_types=1);

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    // Lifecycle states
    const ESTADO_TRIAL      = 'trial';
    const ESTADO_ACTIVO     = 'activo';
    const ESTADO_SUSPENDIDO = 'suspendido';
    const ESTADO_VENCIDO    = 'vencido';
    const ESTADO_CANCELADO  = 'cancelado';

    protected $casts = [
        'activo' => 'boolean',
        'fecha_vencimiento' => 'date',
        'data' => 'array',
        'grace_days' => 'integer',
    ];

    public static function boot(): void
    {
        parent::boot();

        // Force string key — GeneratesIds trait sets incrementing=true
        static::creating(function (self $model) {
            $model->incrementing = false;
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'body_id',
            'nombre',
            'numero',
            'plan',
            'activo',
            'estado',
            'fecha_vencimiento',
            'grace_days',
        ];
    }

    public function body()
    {
        return $this->belongsTo(Body::class);
    }

    /**
     * Check if the tenant is in an operational state (can access the app).
     */
    public function isOperational(): bool
    {
        return in_array($this->estado, [self::ESTADO_TRIAL, self::ESTADO_ACTIVO]);
    }

    /**
     * Check if the tenant is in grace period (vencido but within grace_days).
     */
    public function isInGracePeriod(): bool
    {
        if ($this->estado !== self::ESTADO_VENCIDO || !$this->fecha_vencimiento) {
            return false;
        }

        $graceDays = $this->grace_days ?? 5;
        $graceEnd = $this->fecha_vencimiento->addDays($graceDays);

        return now()->lte($graceEnd);
    }

    /**
     * Get days until expiration (negative = overdue).
     */
    public function daysUntilExpiry(): ?int
    {
        if (!$this->fecha_vencimiento) return null;
        return (int) now()->startOfDay()->diffInDays($this->fecha_vencimiento->startOfDay(), false);
    }

    /**
     * Get the human-readable status label.
     */
    public function estadoLabel(): string
    {
        return match($this->estado) {
            self::ESTADO_TRIAL      => 'Trial',
            self::ESTADO_ACTIVO     => 'Activo',
            self::ESTADO_SUSPENDIDO => 'Suspendido',
            self::ESTADO_VENCIDO    => 'Vencido',
            self::ESTADO_CANCELADO  => 'Cancelado',
            default                 => ucfirst($this->estado ?? 'desconocido'),
        };
    }

    /**
     * Get the CSS class for the status badge.
     */
    public function estadoBadgeClass(): string
    {
        return match($this->estado) {
            self::ESTADO_TRIAL      => 'bg-blue-50 text-blue-700',
            self::ESTADO_ACTIVO     => 'bg-emerald-50 text-emerald-700',
            self::ESTADO_SUSPENDIDO => 'bg-amber-50 text-amber-700',
            self::ESTADO_VENCIDO    => 'bg-red-50 text-red-700',
            self::ESTADO_CANCELADO  => 'bg-slate-100 text-slate-500',
            default                 => 'bg-slate-100 text-slate-600',
        };
    }
}
