<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'user_id',
        'firefighter_id',
        'guardia_id',
        'metadata',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function firefighter(): BelongsTo
    {
        return $this->belongsTo(Bombero::class, 'firefighter_id');
    }

    public function guardia(): BelongsTo
    {
        return $this->belongsTo(Guardia::class);
    }

    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForRoles($query, array $roles)
    {
        return $query; // Filter in controller based on user role
    }

    public static function getUnreadCountForUser(User $user): int
    {
        return self::unread()->count();
    }
}
