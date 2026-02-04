<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardiaAttendanceRecord extends Model
{
    protected $fillable = ['guardia_id', 'date', 'saved_by_user_id', 'saved_at'];

    protected $casts = [
        'date' => 'date',
        'saved_at' => 'datetime',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function savedBy()
    {
        return $this->belongsTo(User::class, 'saved_by_user_id');
    }
}
