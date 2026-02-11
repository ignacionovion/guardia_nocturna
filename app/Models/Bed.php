<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\BedAssignment;

class Bed extends Model
{
    protected $fillable = ['number', 'status', 'description'];

    public function assignments()
    {
        return $this->hasMany(BedAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(BedAssignment::class)->whereNull('released_at')->latestOfMany();
    }
}
