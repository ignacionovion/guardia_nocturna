<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardia;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SystemUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('roleEntity')->where('role', '!=', 'bombero');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $guardias = Guardia::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('guardias', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in(['super_admin', 'capitania', 'guardia', 'jefe_guardia', 'inventario'])],
            'guardia_id' => ['nullable', 'exists:guardias,id'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'guardia_id' => $validated['guardia_id'] ?? null,
            'role_id' => $validated['role_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'age' => 0,
            'years_of_service' => 0,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $guardias = Guardia::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'guardias', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['super_admin', 'capitania', 'guardia', 'jefe_guardia', 'inventario'])],
            'guardia_id' => ['nullable', 'exists:guardias,id'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'guardia_id' => $validated['guardia_id'] ?? null,
            'role_id' => $validated['role_id'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ((int) $user->id === (int) auth()->id()) {
            return back()->withErrors(['msg' => 'No puedes eliminar tu propia cuenta.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
