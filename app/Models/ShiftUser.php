<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Bombero;

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
        'confirmed_at',
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
        'confirmed_at' => 'datetime',
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
        return $this->belongsTo(Bombero::class, 'firefighter_id');
    }

    public function replacedUser()
    {
        return $this->belongsTo(User::class, 'replaced_user_id');
    }

    public function replacedFirefighter()
    {
        return $this->belongsTo(Bombero::class, 'replaced_firefighter_id');
    }
}
