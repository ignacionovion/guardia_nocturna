<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirefighterReplacement extends Model
{
    protected $fillable = [
        'guardia_id',
        'original_firefighter_id',
        'replacement_firefighter_id',
        'starts_at',
        'ends_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function originalFirefighter()
    {
        return $this->belongsTo(Firefighter::class, 'original_firefighter_id');
    }

    public function replacementFirefighter()
    {
        return $this->belongsTo(Firefighter::class, 'replacement_firefighter_id');
    }
}
