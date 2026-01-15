<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CleaningTask extends Model
{
    protected $fillable = ['name', 'description'];

    public function assignments()
    {
        return $this->hasMany(CleaningAssignment::class);
    }
}
