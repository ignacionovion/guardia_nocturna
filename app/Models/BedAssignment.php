<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BedAssignment extends Model
{
    protected $fillable = [
        'bed_id',
        'volunteer_id',
        'firefighter_id',
        'user_id',
        'assigned_by',
        'started_at',
        'ended_at',
        'assigned_at',
        'released_at',
        'notes',
        'assigned_source',
        'assigned_ip',
        'assigned_user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
        'assigned_by' => 'integer',
        'volunteer_id' => 'integer',
        'firefighter_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function volunteer()
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function firefighter()
    {
        return $this->belongsTo(Bombero::class, 'firefighter_id');
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
