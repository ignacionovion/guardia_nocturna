<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novelty extends Model
{
    protected $fillable = ['user_id', 'firefighter_id', 'title', 'description', 'date', 'type', 'guardia_id', 'is_permanent'];

    protected $casts = [
        'date' => 'datetime',
        'is_permanent' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function firefighter()
    {
        return $this->belongsTo(Bombero::class, 'firefighter_id');
    }

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    /**
     * Scope para obtener novedades permanentes (visibles para todas las guardias)
     */
    public function scopePermanent($query)
    {
        return $query->where('is_permanent', true);
    }

    /**
     * Scope para obtener novedades de una guardia específica
     */
    public function scopeByGuardia($query, $guardiaId)
    {
        return $query->where(function ($q) use ($guardiaId) {
            $q->where('guardia_id', $guardiaId)
              ->orWhere('is_permanent', true);
        });
    }

    /**
     * Scope para excluir academias de la bitácora de novedades
     */
    public function scopeNotAcademy($query)
    {
        return $query->where('type', '!=', 'Academia');
    }

    /**
     * Scope para obtener solo academias
     */
    public function scopeAcademy($query)
    {
        return $query->where('type', 'Academia');
    }
}
