<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryWarehouse extends Model
{
    protected $table = 'bodegas';

    protected $fillable = [
        'nombre',
        'ubicacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'bodega_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'bodega_id');
    }
}
