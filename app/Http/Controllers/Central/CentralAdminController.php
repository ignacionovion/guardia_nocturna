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

k<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CentralAdminController extends Controller
{
    public function index()
    {
        $admins = CentralAdmin::orderBy('id')->get();
        return view('central.admins.index', compact('admins'));
    }

    public function create()
    {
        return view('central.admins.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:central_admins,username',
            'password' => 'required|string|min:6',
        ]);

        CentralAdmin::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'activo' => $request->has('activo'),
            'is_super_admin' => $request->has('is_super_admin'),
        ]);

        return redirect()->route('central.admins.index')->with('success', 'Administrador creado');
    }

    public function edit($id)
    {
        $admin = CentralAdmin::findOrFail($id);
        return view('central.admins.edit', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        $admin = CentralAdmin::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:central_admins,username,' . $admin->id,
            'password' => 'nullable|string|min:6',
        ]);

        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->activo = $request->has('activo');
        $admin->is_super_admin = $request->has('is_super_admin');

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return redirect()->route('central.admins.index')->with('success', 'Administrador actualizado');
    }

    public function destroy($id)
    {
        $admin = CentralAdmin::findOrFail($id);

        // ❌ no borrar si es el último admin
        if (CentralAdmin::count() <= 1) {
            return back()->withErrors('No puedes eliminar el último administrador');
        }

        // ❌ no eliminar último super admin
        if ($admin->is_super_admin && CentralAdmin::where('is_super_admin', true)->count() <= 1) {
            return back()->withErrors('Debe existir al menos un super admin');
        }

        $admin->delete();

        return redirect()->route('central.admins.index')->with('success', 'Administrador eliminado');
    }
}
