<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novelty extends Model
{
    protected $fillable = ['user_id', 'title', 'description', 'date', 'type'];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
