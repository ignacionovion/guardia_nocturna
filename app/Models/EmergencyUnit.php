<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyUnit extends Model
{
    protected $fillable = ['name', 'description', 'status', 'out_of_service_reason'];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
