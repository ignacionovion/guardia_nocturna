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

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'activo' => 'boolean',
        'fecha_vencimiento' => 'date',
        'data' => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'body_id',
            'nombre',
            'numero',
            'plan',
            'activo',
            'fecha_vencimiento',
        ];
    }

    public function body()
    {
        return $this->belongsTo(Body::class);
    }
}
