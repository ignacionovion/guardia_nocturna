<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirefighterUserLegacyMap extends Model
{
    protected $fillable = ['firefighter_id', 'user_id'];

    public function firefighter()
    {
        return $this->belongsTo(Firefighter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
