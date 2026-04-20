<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class CentralAdminController extends Controller
{
    public function index(): View
    {
        $admins = CentralAdmin::query()
            ->orderBy('name')
            ->get();

        return view('central.admins.index', compact('admins'));
    }

    public function create(): View
    {
        return view('central.admins.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:190', 'regex:/^[^\s]+$/u', Rule::unique(CentralAdmin::class, 'username')],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(CentralAdmin::class, 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $activo = $request->boolean('activo');
        $isSuper = $request->boolean('is_super_admin');

        $this->validateNewAdmin($activo, $isSuper);

        CentralAdmin::query()->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'activo' => $activo,
            'is_super_admin' => $isSuper,
        ]);

        return redirect('/admin/admins')->with('success', 'Usuario SaaS creado.');
    }

    public function edit(CentralAdmin $admin): View
    {
        return view('central.admins.edit', ['admin' => $admin]);
    }

    public function update(Request $request, CentralAdmin $admin): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:190', 'regex:/^[^\s]+$/u', Rule::unique(CentralAdmin::class, 'username')->ignore($admin->id)],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique(CentralAdmin::class, 'email')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $newActivo = $request->boolean('activo');
        $newSuper = $request->boolean('is_super_admin');

        $this->assertCanChangeActivation($admin, $newActivo);
        $this->assertCanChangeSuperAdmin($admin, $newSuper);

        $admin->name = $data['name'];
        $admin->username = $data['username'];
        $admin->email = $data['email'] ?? null;
        $admin->activo = $newActivo;
        $admin->is_super_admin = $newSuper;

        if (! empty($data['password'])) {
            $admin->password = Hash::make($data['password']);
        }

        $admin->save();

        return redirect('/admin/admins')->with('success', 'Usuario actualizado.');
    }

    public function destroy(CentralAdmin $admin): RedirectResponse
    {
        if ($admin->is(Auth::guard('central')->user())) {
            return redirect('/admin/admins')->with('error', 'No podés eliminar tu propia sesión desde aquí.');
        }

        if ($admin->activo) {
            $otherActiveAdmins = CentralAdmin::query()
                ->whereKeyNot($admin->id)
                ->where('activo', true)
                ->count();

            if ($otherActiveAdmins < 1) {
                return redirect('/admin/admins')->with('error', 'No podés eliminar al único administrador activo del panel central.');
            }

            if ($admin->is_super_admin) {
                $otherActiveSupers = CentralAdmin::query()
                    ->whereKeyNot($admin->id)
                    ->where('activo', true)
                    ->where('is_super_admin', true)
                    ->count();

                if ($otherActiveSupers < 1) {
                    return redirect('/admin/admins')->with('error', 'No podés eliminar al único super administrador activo.');
                }
            }
        }

        $admin->delete();

        return redirect('/admin/admins')->with('success', 'Usuario eliminado.');
    }

    private function validateNewAdmin(bool $activo, bool $isSuper): void
    {
        if (! $activo) {
            $otherActive = CentralAdmin::query()->where('activo', true)->count();
            if ($otherActive < 1) {
                throw ValidationException::withMessages([
                    'activo' => 'No podés crear un usuario inactivo si no hay otros administradores activos.',
                ]);
            }
        }

        if (! $isSuper) {
            $supers = CentralAdmin::query()->where('activo', true)->where('is_super_admin', true)->count();
            if ($supers < 1) {
                throw ValidationException::withMessages([
                    'is_super_admin' => 'Debés tener al menos un super administrador activo antes de crear usuarios sin ese rol.',
                ]);
            }
        }
    }

    private function assertCanChangeActivation(CentralAdmin $admin, bool $newActivo): void
    {
        if ($admin->activo && ! $newActivo) {
            $others = CentralAdmin::query()
                ->whereKeyNot($admin->id)
                ->where('activo', true)
                ->count();
            if ($others < 1) {
                throw ValidationException::withMessages([
                    'activo' => 'No podés desactivar al único administrador activo.',
                ]);
            }

            if ($admin->is_super_admin) {
                $otherSupers = CentralAdmin::query()
                    ->whereKeyNot($admin->id)
                    ->where('activo', true)
                    ->where('is_super_admin', true)
                    ->count();
                if ($otherSupers < 1) {
                    throw ValidationException::withMessages([
                        'activo' => 'No podés desactivar al único super administrador activo.',
                    ]);
                }
            }
        }
    }

    private function assertCanChangeSuperAdmin(CentralAdmin $admin, bool $newSuper): void
    {
        if ($admin->is_super_admin && ! $newSuper) {
            $others = CentralAdmin::query()
                ->whereKeyNot($admin->id)
                ->where('activo', true)
                ->where('is_super_admin', true)
                ->count();
            if ($others < 1) {
                throw ValidationException::withMessages([
                    'is_super_admin' => 'No podés quitar el rol super administrador al único que queda.',
                ]);
            }
        }
    }
}
