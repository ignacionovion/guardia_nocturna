<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $table = 'inventario_items';

    protected $fillable = [
        'bodega_id',
        'categoria',
        'titulo',
        'variante',
        'unidad',
        'stock',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function warehouse()
    {
        return $this->belongsTo(InventoryWarehouse::class, 'bodega_id');
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'item_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $base = (string) $this->titulo;
        $variant = trim((string) ($this->variante ?? ''));
        if ($variant !== '') {
            return $base . ' — ' . $variant;
        }
        return $base;
    }
}
