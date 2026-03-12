<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'payments';

    protected $fillable = [
        'tenant_id',
        'monto',
        'fecha_pago',
        'metodo_pago',
        'observacion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class, 'tenant_id', 'tenant_id');
    }
}
