<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['date', 'shift_leader_id', 'status', 'notes'];

    protected $casts = [
        'date' => 'date',
    ];

    public function leader()
    {
        return $this->belongsTo(User::class, 'shift_leader_id');
    }

    public function users()
    {
        return $this->hasMany(ShiftUser::class);
    }
}
