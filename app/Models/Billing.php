<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Facturación SaaS (`tenant_billing`).
 *
 * Fuente de verdad operativa para monto, ciclo, estado de pago y fechas de cobro/trial.
 * Use `syncToTenant()` para alinear `tenants` (plan_id, plan slug legacy, fecha_vencimiento, estado, activo)
 * y corregir `tenant_billing.plan` si difiere del slug del plan asociado a `plan_id`.
 */
class Billing extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'tenant_billing';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'plan',
        'billing_cycle',
        'monto',
        'estado_pago',
        'fecha_vencimiento',
        'fecha_ultimo_pago',
        'trial_ends_at',
        'observacion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'billing_cycle' => 'string',
        'fecha_vencimiento' => 'date',
        'fecha_ultimo_pago' => 'date',
        'trial_ends_at' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function planRelation(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Scope for pending payments
     */
    public function scopePendientes($query)
    {
        return $query->where('estado_pago', 'pendiente');
    }

    /**
     * Scope for expired payments
     */
    public function scopeVencidos($query)
    {
        return $query->where('estado_pago', 'vencido');
    }

    /**
     * Scope for expiring within days
     */
    public function scopePorVencer($query, int $dias = 7)
    {
        return $query->where('fecha_vencimiento', '<=', now()->addDays($dias))
            ->where('fecha_vencimiento', '>=', now());
    }

    /**
     * Scope for trial status
     */
    public function scopeTrial($query)
    {
        return $query->where('estado_pago', 'trial');
    }

    /**
     * Check if trial has ended
     */
    public function trialTerminado(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * End trial and convert to pending
     */
    public function finalizarTrial(): void
    {
        $this->update([
            'estado_pago' => 'pendiente',
            'fecha_vencimiento' => now()->addDays(30),
            'trial_ends_at' => null,
        ]);
        $this->refresh();
        $this->syncToTenant();
    }

    /**
     * Get cycle label
     */
    public function getCicloEtiqueta(): string
    {
        return match($this->billing_cycle) {
            'monthly' => 'Mensual',
            'yearly' => 'Anual',
            default => 'Mensual',
        };
    }

    /**
     * Get cycle badge class
     */
    public function getCicloClase(): string
    {
        return match($this->billing_cycle) {
            'monthly' => 'bg-blue-100 text-blue-800 border-blue-200',
            'yearly' => 'bg-purple-100 text-purple-800 border-purple-200',
            default => 'bg-blue-100 text-blue-800 border-blue-200',
        };
    }

    /**
     * Check if billing is expired
     */
    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast();
    }

    /**
     * Mark as paid and extend due date based on billing cycle
     */
    public function marcarPagado(?string $fechaPago = null): void
    {
        $fecha = $fechaPago ? \Carbon\Carbon::parse($fechaPago) : now();
        $dias = $this->billing_cycle === 'yearly' ? 365 : 30;

        $this->update([
            'estado_pago' => 'pagado',
            'fecha_ultimo_pago' => $fecha,
            'fecha_vencimiento' => $fecha->copy()->addDays($dias),
        ]);
        $this->refresh();
        $this->syncToTenant();
    }

    /**
     * Extend due date by days
     */
    public function extenderVencimiento(int $dias): void
    {
        $nuevaFecha = $this->fecha_vencimiento
            ? $this->fecha_vencimiento->copy()->addDays($dias)
            : now()->addDays($dias);

        $this->update([
            'fecha_vencimiento' => $nuevaFecha,
            'estado_pago' => $this->estado_pago === 'vencido' ? 'pendiente' : $this->estado_pago,
        ]);
        $this->refresh();
        $this->syncToTenant();
    }

    /**
     * Suspend tenant
     */
    public function suspender(): void
    {
        $this->update(['estado_pago' => 'suspendido']);
        $this->refresh();
        $this->syncToTenant();
    }

    /**
     * Alinea el registro central del tenant con este billing (plan_id, plan slug legacy, fechas, acceso).
     * Convención: `tenant_billing` define el estado comercial; `tenants` refleja acceso y calendario.
     * Los campos string `plan` (slug) son legacy frente a `plan_id`; se mantienen sincronizados mientras existan en BD.
     */
    public function syncToTenant(): void
    {
        $this->loadMissing('tenant', 'planRelation');
        $tenant = $this->tenant;
        if (!$tenant) {
            return;
        }

        $planSlug = $this->resolvePlanSlugForSync();

        if ($this->plan_id && $planSlug !== null && (string) $this->plan !== (string) $planSlug) {
            self::query()->whereKey($this->getKey())->update(['plan' => $planSlug]);
            $this->setAttribute('plan', $planSlug);
        }

        [$estado, $activo] = $this->resolveTenantEstadoYActivo();

        $payload = [
            'estado' => $estado,
            'activo' => $activo,
            'fecha_vencimiento' => $this->resolveTenantFechaVencimiento(),
        ];

        if ($this->plan_id) {
            $payload['plan_id'] = $this->plan_id;
        }

        if ($planSlug !== null) {
            $payload['plan'] = $planSlug;
        }

        $tenant->update($payload);
    }

    /**
     * Slug del plan coherente con `plan_id` (o fallback al string `plan` del billing).
     */
    private function resolvePlanSlugForSync(): ?string
    {
        if ($this->plan_id) {
            if ($this->relationLoaded('planRelation') && $this->planRelation) {
                return (string) $this->planRelation->slug;
            }

            $slug = Plan::query()->whereKey($this->plan_id)->value('slug');
            if ($slug !== null && $slug !== '') {
                return (string) $slug;
            }
        }

        if ($this->plan !== null && $this->plan !== '') {
            return (string) $this->plan;
        }

        return null;
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function resolveTenantEstadoYActivo(): array
    {
        return match ($this->estado_pago) {
            'trial' => [Tenant::ESTADO_TRIAL, true],
            'pagado', 'pendiente' => [Tenant::ESTADO_ACTIVO, true],
            'vencido' => [Tenant::ESTADO_VENCIDO, true],
            'suspendido' => [Tenant::ESTADO_SUSPENDIDO, false],
            default => [Tenant::ESTADO_ACTIVO, true],
        };
    }

    private function resolveTenantFechaVencimiento(): ?Carbon
    {
        if ($this->estado_pago === 'trial' && $this->trial_ends_at) {
            return $this->trial_ends_at->copy()->startOfDay();
        }

        return $this->fecha_vencimiento?->copy()->startOfDay();
    }

    /**
     * Get badge color for status
     */
    public function getEstadoColor(): string
    {
        return match($this->estado_pago) {
            'pagado' => 'green',
            'pendiente' => 'yellow',
            'vencido' => 'red',
            'suspendido' => 'gray',
            'trial' => 'blue',
            default => 'gray',
        };
    }

    /**
     * Get badge class for status
     */
    public function getEstadoClase(): string
    {
        return match($this->estado_pago) {
            'pagado' => 'bg-green-100 text-green-800 border-green-200',
            'pendiente' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'vencido' => 'bg-red-100 text-red-800 border-red-200',
            'suspendido' => 'bg-gray-100 text-gray-800 border-gray-200',
            'trial' => 'bg-blue-100 text-blue-800 border-blue-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
