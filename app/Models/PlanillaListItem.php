<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanillaListItem extends Model
{
    protected $table = 'planilla_list_items';

    protected $fillable = [
        'unidad',
        'section',
        'item_key',
        'label',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
