<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Bombero;
use App\Models\User;

class StaffEvent extends Model
{
    protected $fillable = [
        'user_id',
        'firefighter_id',
        'type',
        'start_date',
        'end_date',
        'description',
        'status',
        'replacement_user_id',
        'replacement_firefighter_id',
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

    public function firefighter()
    {
        return $this->belongsTo(Bombero::class, 'firefighter_id');
    }

    public function replacementFirefighter()
    {
        return $this->belongsTo(Bombero::class, 'replacement_firefighter_id');
    }
}
