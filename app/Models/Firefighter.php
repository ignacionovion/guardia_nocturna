<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firefighter extends Model
{
    protected $fillable = [
        'guardia_id',
        'name',
        'last_name_paternal',
        'last_name_maternal',
        'rut',
        'email',
        'registration_number',
        'address_street',
        'address_number',
        'birthdate',
        'admission_date',
        'position_text',
        'portable_number',
        'is_driver',
        'is_rescue_operator',
        'is_trauma_assistant',
        'is_shift_leader',
        'attendance_status',
        'is_titular',
        'is_exchange',
        'is_penalty',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'admission_date' => 'date',
        'is_driver' => 'boolean',
        'is_rescue_operator' => 'boolean',
        'is_trauma_assistant' => 'boolean',
        'is_shift_leader' => 'boolean',
        'is_titular' => 'boolean',
        'is_exchange' => 'boolean',
        'is_penalty' => 'boolean',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function legacyUserMap()
    {
        return $this->hasOne(FirefighterUserLegacyMap::class);
    }
}
