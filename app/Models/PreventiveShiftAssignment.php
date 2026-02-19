<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreventiveShiftAssignment extends Model
{
    protected $fillable = [
        'preventive_shift_id',
        'bombero_id',
        'es_refuerzo',
        'entrada_hora',
        'reemplaza_a_bombero_id',
    ];

    protected $casts = [
        'es_refuerzo' => 'boolean',
        'entrada_hora' => 'datetime',
    ];

    public function shift()
    {
        return $this->belongsTo(PreventiveShift::class, 'preventive_shift_id');
    }

    public function firefighter()
    {
        return $this->belongsTo(Bombero::class, 'bombero_id');
    }

    public function attendance()
    {
        return $this->hasOne(PreventiveShiftAttendance::class, 'preventive_shift_assignment_id');
    }

    public function replacedFirefighter()
    {
        return $this->belongsTo(Bombero::class, 'reemplaza_a_bombero_id');
    }
}
