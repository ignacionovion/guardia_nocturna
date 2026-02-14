<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreventiveShiftTemplate extends Model
{
    protected $fillable = [
        'preventive_event_id',
        'sort_order',
        'start_time',
        'end_time',
        'label',
    ];

    protected $casts = [];

    public function event()
    {
        return $this->belongsTo(PreventiveEvent::class, 'preventive_event_id');
    }

    public function shifts()
    {
        return $this->hasMany(PreventiveShift::class, 'template_id');
    }
}
