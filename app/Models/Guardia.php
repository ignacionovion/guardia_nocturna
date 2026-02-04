<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardia extends Model
{
    protected $fillable = ['name', 'is_active_week'];

    protected $casts = [
        'is_active_week' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function firefighters()
    {
        return $this->hasMany(Firefighter::class);
    }
}
