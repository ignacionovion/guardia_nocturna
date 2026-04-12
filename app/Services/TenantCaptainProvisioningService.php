<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantCaptainProvisioningService
{
    /**
     * Create capitan user only if it does not exist. Does not overwrite existing users.
     *
     * @return array{skipped: bool, username: string, plain_password: ?string}
     */
    public function provisionInitialAccess(Tenant $tenant): array
    {
        return $tenant->run(function () {
            $existing = User::query()->where('username', 'capitan')->first();
            if ($existing !== null) {
                return [
                    'skipped' => true,
                    'username' => 'capitan',
                    'plain_password' => null,
                ];
            }

            $plain = Str::password(16);

            User::create([
                'name' => 'Capitán',
                'username' => 'capitan',
                'email' => 'capitan@system.local',
                'password' => Hash::make($plain),
                'password_must_change' => true,
                'role' => 'capitan',
                'age' => 0,
                'years_of_service' => 0,
            ]);

            return [
                'skipped' => false,
                'username' => 'capitan',
                'plain_password' => $plain,
            ];
        });
    }

    /**
     * Reset or create capitan user with a new random password.
     *
     * @return array{username: string, plain_password: string}
     */
    public function resetCaptainPassword(Tenant $tenant): array
    {
        return $tenant->run(function () {
            $plain = Str::password(16);

            $user = User::query()->where('username', 'capitan')->first();

            if ($user === null) {
                $user = new User([
                    'name' => 'Capitán',
                    'username' => 'capitan',
                    'email' => 'capitan@system.local',
                    'role' => 'capitan',
                    'age' => 0,
                    'years_of_service' => 0,
                ]);
            }

            $user->password = Hash::make($plain);
            $user->password_must_change = true;
            $user->remember_token = Str::random(60);
            $user->save();

            $this->invalidateUserSessions((int) $user->id);

            return [
                'username' => 'capitan',
                'plain_password' => $plain,
            ];
        });
    }

    private function invalidateUserSessions(int $userId): void
    {
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            DB::table('sessions')->where('user_id', $userId)->delete();
        }
    }
}
