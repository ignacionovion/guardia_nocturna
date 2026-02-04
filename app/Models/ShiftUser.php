<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftUser extends Model
{
    protected $fillable = [
        'shift_id', 
        'user_id', 
        'firefighter_id',
        'guardia_id',
        'role', 
        'present',
        'attendance_status',
        'assignment_type',
        'replaced_user_id',
        'replaced_firefighter_id',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'present' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function firefighter()
    {
        return $this->belongsTo(Firefighter::class);
    }

    public function replacedUser()
    {
        return $this->belongsTo(User::class, 'replaced_user_id');
    }

    public function replacedFirefighter()
    {
        return $this->belongsTo(Firefighter::class, 'replaced_firefighter_id');
    }
}
