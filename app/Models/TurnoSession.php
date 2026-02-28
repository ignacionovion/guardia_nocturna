<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoSession extends Model
{
    protected $table = 'turno_sessions';

    protected $fillable = [
        'guardia_id',
        'shift_id',
        'operational_date',
        'opened_at',
        'close_at',
        'status',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'operational_date' => 'date',
        'opened_at' => 'datetime',
        'close_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(TurnoSessionItem::class, 'turno_session_id');
    }

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
