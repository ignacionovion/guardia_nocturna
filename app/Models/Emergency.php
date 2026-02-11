<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Bombero;
use App\Models\User;

class Emergency extends Model
{
    protected $fillable = [
        'emergency_key_id',
        'dispatched_at',
        'arrived_at',
        'details',
        'shift_id',
        'guardia_id',
        'officer_in_charge_user_id',
        'officer_in_charge_firefighter_id',
        'created_by',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'arrived_at' => 'datetime',
    ];

    public function key()
    {
        return $this->belongsTo(EmergencyKey::class, 'emergency_key_id');
    }

    public function units()
    {
        return $this->belongsToMany(
            EmergencyUnit::class,
            'emergency_emergency_unit',
            'emergency_id',
            'emergency_unit_id'
        );
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function officerInCharge()
    {
        return $this->belongsTo(User::class, 'officer_in_charge_user_id');
    }

    public function officerInChargeFirefighter()
    {
        return $this->belongsTo(Bombero::class, 'officer_in_charge_firefighter_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
