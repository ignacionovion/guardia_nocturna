<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffEvent extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'description',
        'status',
        'replacement_user_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replacementUser()
    {
        return $this->belongsTo(User::class, 'replacement_user_id');
    }
}
