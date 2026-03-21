<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class Guardia extends Model
{
    protected $fillable = [
        'name',
        'is_active_week',
        'slug',
        'access_username',
        'access_password_encrypted',
        'user_id',
    ];

    protected $casts = [
        'is_active_week' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function bomberos()
    {
        return $this->hasMany(Bombero::class);
    }

    public function firefighters()
    {
        return $this->bomberos();
    }

    public function accessUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getPlainPasswordAttribute(): ?string
    {
        if (!$this->access_password_encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($this->access_password_encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function generateUsername(string $tenantId, string $slug): string
    {
        return $tenantId . '-' . $slug;
    }

    public static function generateSlug(string $name): string
    {
        return Str::slug($name) ?: 'guardia';
    }
}
