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
        abort(403, 'La creación de roles del sistema no está permitida. El sistema usa únicamente los roles CAPITÁN y GUARDIA.');
    }

    public function store(Request $request)
    {
        abort(403, 'La creación de roles del sistema no está permitida.');
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
