<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Validation\ValidationException;

class CentralAdmin extends Authenticatable
{
    protected $connection = 'central';

    protected $table = 'central_admins';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'activo',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (CentralAdmin $admin): void {
            if (! $admin->is_super_admin || ! $admin->activo) {
                return;
            }

            $otherActiveSuperExists = self::query()
                ->whereKeyNot($admin->id)
                ->where('activo', true)
                ->where('is_super_admin', true)
                ->exists();

            if (! $otherActiveSuperExists) {
                throw ValidationException::withMessages([
                    'admin' => 'No podés eliminar al último super administrador activo.',
                ]);
            }
        });

        static::updating(function (CentralAdmin $admin): void {
            $wasSuper = (bool) $admin->getOriginal('is_super_admin');
            $wasActive = (bool) $admin->getOriginal('activo');
            $willBeSuper = (bool) $admin->is_super_admin;
            $willBeActive = (bool) $admin->activo;

            $isLosingActiveSuperState = $wasSuper && $wasActive && (! $willBeSuper || ! $willBeActive);
            if (! $isLosingActiveSuperState) {
                return;
            }

            $otherActiveSuperExists = self::query()
                ->whereKeyNot($admin->id)
                ->where('activo', true)
                ->where('is_super_admin', true)
                ->exists();

            if (! $otherActiveSuperExists) {
                throw ValidationException::withMessages([
                    'is_super_admin' => 'No podés desactivar al último super administrador activo.',
                ]);
            }
        });
    }
}
