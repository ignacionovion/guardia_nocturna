<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Billing extends Model
{
    use HasFactory;

    protected $table = 'tenant_billing';

    protected $fillable = [
        'tenant_id',
        'plan',
        'monto',
        'estado_pago',
        'fecha_vencimiento',
        'fecha_ultimo_pago',
        'trial_ends_at',
        'observacion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_ultimo_pago' => 'date',
        'trial_ends_at' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
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
    }

    /**
     * Check if billing is expired
     */
    public function estaVencido(): bool
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast();
    }

    /**
     * Mark as paid and extend due date
     */
    public function marcarPagado(): void
    {
        $this->update([
            'estado_pago' => 'pagado',
            'fecha_ultimo_pago' => now(),
            'fecha_vencimiento' => now()->addMonth(),
        ]);
    }

    /**
     * Extend due date by days
     */
    public function extenderVencimiento(int $dias): void
    {
        $nuevaFecha = $this->fecha_vencimiento
            ? $this->fecha_vencimiento->addDays($dias)
            : now()->addDays($dias);

        $this->update([
            'fecha_vencimiento' => $nuevaFecha,
            'estado_pago' => $this->estado_pago === 'vencido' ? 'pendiente' : $this->estado_pago,
        ]);
    }

    /**
     * Suspend tenant
     */
    public function suspender(): void
    {
        $this->update(['estado_pago' => 'suspendido']);

        // Also suspend the tenant itself
        $this->tenant->update(['activo' => false]);
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
