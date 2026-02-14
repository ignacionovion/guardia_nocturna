<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planilla extends Model
{
    protected $table = 'planillas';

    protected $fillable = [
        'unidad',
        'fecha_revision',
        'created_by',
        'data',
        'estado',
    ];

    protected $casts = [
        'fecha_revision' => 'datetime',
        'data' => 'array',
    ];

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
