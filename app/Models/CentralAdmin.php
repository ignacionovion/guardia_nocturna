<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

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
}
