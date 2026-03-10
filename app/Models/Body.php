<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Body extends Model
{
    protected $table = 'bodies';

    protected $fillable = [
        'nombre',
        'ciudad',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'body_id');
    }

    public function companies()
    {
        return $this->tenants();
    }
}
