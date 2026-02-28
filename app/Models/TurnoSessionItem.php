<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoSessionItem extends Model
{
    protected $table = 'turno_session_items';

    protected $fillable = [
        'turno_session_id',
        'firefighter_id',
        'included',
        'removed_at',
        'attendance_status',
        'confirm_token',
        'confirmed_at',
        'confirmed_by_user_id',
        'bed_id',
    ];

    protected $casts = [
        'included' => 'boolean',
        'removed_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(TurnoSession::class, 'turno_session_id');
    }

    public function firefighter()
    {
        return $this->belongsTo(Bombero::class, 'firefighter_id');
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }
}
