<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreventiveShiftAttendance extends Model
{
    protected $fillable = [
        'preventive_shift_assignment_id',
        'status',
        'confirmed_at',
        'confirm_ip',
        'confirm_user_agent',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function assignment()
    {
        return $this->belongsTo(PreventiveShiftAssignment::class, 'preventive_shift_assignment_id');
    }
}
