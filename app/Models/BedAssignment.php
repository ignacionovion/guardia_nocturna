<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Bombero;
use App\Models\User;

class BedAssignment extends Model
{
    protected $fillable = [
        'bed_id',
        'user_id',
        'firefighter_id',
        'assigned_at',
        'released_at',
        'notes',
        'assigned_source',
        'assigned_ip',
        'assigned_user_agent',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function firefighter()
    {
        return $this->belongsTo(Bombero::class, 'firefighter_id');
    }
}
