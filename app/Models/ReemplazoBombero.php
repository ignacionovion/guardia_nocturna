<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReemplazoBombero extends Model
{
    protected $table = 'reemplazos_bomberos';

    protected $fillable = [
        'guardia_id',
        'bombero_titular_id',
        'bombero_reemplazante_id',
        'inicio',
        'fin',
        'estado',
        'notas',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
    ];

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function originalFirefighter()
    {
        return $this->belongsTo(Bombero::class, 'bombero_titular_id');
    }

    public function replacementFirefighter()
    {
        return $this->belongsTo(Bombero::class, 'bombero_reemplazante_id');
    }
}
