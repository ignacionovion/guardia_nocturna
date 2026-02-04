<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyKey extends Model
{
    protected $fillable = ['code', 'description'];
}
