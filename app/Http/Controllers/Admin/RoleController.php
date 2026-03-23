<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::query()->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $roles = $query->paginate(20);

        return view('admin.roles.index', compact('roles'));
    }

    public function show(string $id)
    {
        $role = Role::with(['users' => function ($q) {
            $q->orderBy('name');
        }])->findOrFail($id);

        return view('admin.roles.show', compact('role'));
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $allowedPermissions = [
            'dashboard',
            'guardias',
            'dotaciones',
            'calendario',
            'voluntarios',
            'usuarios',
            'roles',
            'emergencias',
            'reportes',
            'planillas',
            'inventario',
            'preventivas',
            'camas',
            'novedades',
            'limpieza',
            'academias',
            'admin_system',
        ];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'slug' => ['required', 'string', 'max:255', 'unique:roles,slug', 'regex:/^[a-z0-9_\-]+$/'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($allowedPermissions)],
        ]);

        Role::create([
            'name' => $validated['name'],
            'slug' => strtolower((string) $validated['slug']),
            'permissions' => array_values(array_unique($validated['permissions'] ?? [])),
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $allowedPermissions = [
            'dashboard', 'guardias', 'dotaciones', 'calendario', 'voluntarios',
            'usuarios', 'roles', 'emergencias', 'reportes', 'planillas',
            'inventario', 'preventivas', 'camas', 'novedades', 'limpieza',
            'academias', 'admin_system',
        ];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($role->id), 'regex:/^[a-z0-9_\\-]+$/'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($allowedPermissions)],
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => strtolower((string) $validated['slug']),
            'permissions' => array_values(array_unique($validated['permissions'] ?? [])),
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        if ($role->users()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un rol que tiene usuarios asignados.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}
