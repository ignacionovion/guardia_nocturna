<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BedAssignment extends Model
{
    protected $fillable = [
        'bed_id',
        'volunteer_id',
        'assigned_by',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'assigned_by' => 'integer',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function volunteer()
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('ended_at');
    }

    // Helpers
    public function isActive()
    {
        return is_null($this->ended_at);
    }

    public function getDurationAttribute()
    {
        $end = $this->ended_at ?? now();
        return $this->started_at->diffForHumans($end, true);
    }
}
