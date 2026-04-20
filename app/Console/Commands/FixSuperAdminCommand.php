<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\CentralAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FixSuperAdminCommand extends Command
{
    protected $signature = 'saas:fix-super-admin';

    protected $description = 'Repara inconsistencias de super admin en central_admins';

    public function handle(): int
    {
        $superAdmin = CentralAdmin::query()
            ->where('is_super_admin', true)
            ->orderBy('id')
            ->first();

        if ($superAdmin === null) {
            $superAdmin = CentralAdmin::query()->create([
                'name' => 'Root',
                'username' => 'root',
                'password' => Hash::make('123456'),
                'activo' => true,
                'is_super_admin' => true,
            ]);

            $this->info('No existía super admin: se creó usuario root.');
        } else {
            $username = trim((string) $superAdmin->username);

            if ($username === '') {
                $superAdmin->username = 'root';
                $superAdmin->save();
                $this->info('Super admin con username vacío: actualizado a root.');
            }
        }

        $activeSuperCount = CentralAdmin::query()
            ->where('is_super_admin', true)
            ->where('activo', true)
            ->count();

        if ($activeSuperCount < 1) {
            $superAdmin->activo = true;
            $superAdmin->save();
            $this->info('No había super admin activo: se activó el super admin principal.');
        }

        $removed = 0;
        $emptyUsernameAdmins = CentralAdmin::query()
            ->where(function ($query): void {
                $query->whereNull('username')
                    ->orWhere('username', '');
            })
            ->get();

        foreach ($emptyUsernameAdmins as $admin) {
            if ($admin->id === $superAdmin->id) {
                continue;
            }

            if (trim((string) $admin->username) === '') {
                $admin->delete();
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->warn("Se eliminaron {$removed} usuarios con username vacío.");
        }

        $this->info('Consistencia SaaS central OK: existe al menos un super admin activo.');

        return self::SUCCESS;
    }
}
