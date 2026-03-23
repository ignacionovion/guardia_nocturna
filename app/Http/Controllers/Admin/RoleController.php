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
        abort(403, 'La modificación de roles del sistema no está permitida.');
    }

    public function destroy(string $id)
    {
        abort(403, 'La eliminación de roles del sistema no está permitida.');
    }
}
