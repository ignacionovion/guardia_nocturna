<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryQrLink extends Model
{
    protected $table = 'inventario_qr_links';

    protected $fillable = [
        'token',
        'tipo',
        'bodega_id',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function warehouse()
    {
        return $this->belongsTo(InventoryWarehouse::class, 'bodega_id');
    }
}
