<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function bomberos()
    {
        return $this->belongsToMany(Bombero::class, 'bombero_specialty', 'specialty_id', 'bombero_id')
            ->withTimestamps();
    }
}
