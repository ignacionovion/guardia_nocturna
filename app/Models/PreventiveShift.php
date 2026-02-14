<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreventiveShift extends Model
{
    protected $fillable = [
        'preventive_event_id',
        'template_id',
        'shift_date',
        'start_time',
        'end_time',
        'sort_order',
        'label',
    ];

    protected $casts = [
        'shift_date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(PreventiveEvent::class, 'preventive_event_id');
    }

    public function template()
    {
        return $this->belongsTo(PreventiveShiftTemplate::class, 'template_id');
    }

    public function assignments()
    {
        return $this->hasMany(PreventiveShiftAssignment::class, 'preventive_shift_id');
    }
}
