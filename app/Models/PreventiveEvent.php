<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreventiveEvent extends Model
{
    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'timezone',
        'status',
        'public_token',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function templates()
    {
        return $this->hasMany(PreventiveShiftTemplate::class);
    }

    public function shifts()
    {
        return $this->hasMany(PreventiveShift::class);
    }
}
