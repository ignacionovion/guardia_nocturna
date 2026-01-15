<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BedAssignment extends Model
{
    protected $fillable = ['bed_id', 'user_id', 'assigned_at', 'released_at', 'notes'];

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
}
