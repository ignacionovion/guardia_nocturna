<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bed extends Model
{
    protected $fillable = [
        'name',
        'location',
        'gender',
        'status',
        'notes',
        'qr_token',
        'created_by'
    ];

    protected $casts = [
        'created_by' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bed) {
            if (empty($bed->qr_token)) {
                $bed->qr_token = Str::random(32);
            }
        });
    }

    public function assignments()
    {
        return $this->hasMany(BedAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(BedAssignment::class)->whereNull('ended_at')->latestOfMany();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helpers de ocupación
    public function getIsOccupiedAttribute()
    {
        return $this->currentAssignment()->exists();
    }

    public function getCurrentOccupantNameAttribute()
    {
        $assignment = $this->currentAssignment;
        if (!$assignment || !$assignment->volunteer) {
            return null;
        }
        
        $volunteer = $assignment->volunteer;
        return trim($volunteer->nombres . ' ' . $volunteer->apellido_paterno);
    }

    public function canBeAssigned()
    {
        return !in_array($this->status, ['maintenance', 'disabled']) && !$this->is_occupied;
    }

    // Helpers para labels
    public function getGenderLabelAttribute()
    {
        return match($this->gender) {
            'male' => 'Masculino',
            'female' => 'Femenino',
            'mixed' => 'Mixto',
            default => 'N/A'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'available' => 'Disponible',
            'occupied' => 'Ocupada',
            'maintenance' => 'Mantención',
            'disabled' => 'Deshabilitada',
            default => 'N/A'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'available' => 'success',
            'occupied' => 'warning',
            'maintenance' => 'info',
            'disabled' => 'danger',
            default => 'secondary'
        };
    }

    public function getGenderColorAttribute()
    {
        return match($this->gender) {
            'male' => 'primary',
            'female' => 'danger',
            'mixed' => 'secondary',
            default => 'secondary'
        };
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
