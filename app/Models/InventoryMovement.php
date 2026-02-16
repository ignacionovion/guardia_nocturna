<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $table = 'inventario_movimientos';

    protected $fillable = [
        'bodega_id',
        'item_id',
        'tipo',
        'cantidad',
        'nota',
        'creado_por',
        'bombero_id',
    ];

    public function warehouse()
    {
        return $this->belongsTo(InventoryWarehouse::class, 'bodega_id');
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function firefighter()
    {
        return $this->belongsTo(Bombero::class, 'bombero_id');
    }
}
