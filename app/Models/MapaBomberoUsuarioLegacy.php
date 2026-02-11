<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapaBomberoUsuarioLegacy extends Model
{
    protected $table = 'mapa_bombero_usuario_legacy';

    protected $fillable = [
        'firefighter_id',
        'user_id',
    ];

    public function firefighter()
    {
        return $this->belongsTo(Bombero::class, 'firefighter_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
