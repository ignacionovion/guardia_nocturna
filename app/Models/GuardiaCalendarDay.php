<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardiaCalendarDay extends Model
{
    protected $fillable = ['date', 'guardia_id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }
}
