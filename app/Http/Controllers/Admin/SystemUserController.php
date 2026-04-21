<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardia;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
        $canCreateUser = $this->limitService->canCreateUser();
        $limitData = [
            'can_create' => $canCreateUser,
            'message' => !$canCreateUser ? $this->limitService->getLimitExceededMessage('users') : null,
        ];

        $guardias = Guardia::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('guardias', 'roles', 'limitData'));
    }

    public function __construct(
        protected \App\Services\TenantPlanLimitService $limitService
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'role' => ['required_without:role_id', 'string', Rule::in(['capitan', 'guardia'])],
            'guardia_id' => ['nullable', 'exists:guardias,id'],
            'role_id' => ['required_without:role', 'nullable', 'exists:roles,id'],
        ], [
            'role.required_without' => 'Debe seleccionar un Perfil de Sistema o un Rol Personalizado',
            'role_id.required_without' => 'Debe seleccionar un Perfil de Sistema o un Rol Personalizado',
        ]);

        // XOR validation: solo uno de role o role_id puede estar presente
        if (!empty($validated['role']) && !empty($validated['role_id'])) {
            return back()->withErrors([
                'role' => 'No puede seleccionar Perfil de Sistema y Rol Personalizado simultáneamente',
                'role_id' => 'No puede seleccionar Perfil de Sistema y Rol Personalizado simultáneamente',
            ])->withInput();
        }

        // Auto-generate username from name if not provided
        $username = $validated['username'] ?? $this->generateUsername($validated['name']);
        
        // Ensure username uniqueness
        $username = $this->ensureUniqueUsername($username);

        // Auto-generate secure password
        $password = Str::password(12);

        // Determinar role de sistema: si viene role, usarlo; si viene role_id, usar 'guardia'
        $systemRole = $validated['role'] ?? 'guardia';

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $username,
            'role' => $systemRole,
            'guardia_id' => $validated['guardia_id'] ?? null,
            'role_id' => $validated['role_id'] ?? null,
            'password' => Hash::make($password),
            'age' => 0,
            'years_of_service' => 0,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.')
            ->with('new_user_credentials', [
                'username' => $username,
                'password' => $password,
                'name' => $validated['name'],
            ]);
    }

    public function regeneratePassword(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        // Only capitan and super_admin can regenerate passwords
        if (!in_array(auth()->user()->role, ['capitan', 'super_admin'])) {
            abort(403, 'No tienes permiso para regenerar contraseñas.');
        }

        // Generate new secure password
        $newPassword = Str::password(12);

        // Update password
        $user->update([
            'password' => Hash::make($newPassword),
            'remember_token' => Str::random(60),
        ]);

        // Invalidate active sessions if session driver uses database
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        // Log the action
        Log::info('Password regenerated', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'regenerated_by' => auth()->user()->id,
            'regenerated_by_name' => auth()->user()->name,
            'timestamp' => now(),
        ]);

        return back()->with('regenerated_password', [
            'username' => $user->username,
            'password' => $newPassword,
            'name' => $user->name,
        ]);
    }

    private function generateUsername(string $name): string
    {
        // Convert to lowercase and replace spaces with dots
        $username = strtolower($name);
        $username = str_replace(' ', '.', $username);
        
        // Remove accents
        $username = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $username
        );
        
        // Remove special characters except dots
        $username = preg_replace('/[^a-z0-9.]/', '', $username);
        
        return $username;
    }

    private function ensureUniqueUsernameForUpdate(string $username, int $userId): string
    {
        $original = $username;
        $counter = 1;

        while (User::where('username', $username)->where('id', '!=', $userId)->exists()) {
            $username = $original . '.' . $counter;
            $counter++;
        }

        return $username;
    }

    private function ensureUniqueUsername(string $username): string
    {
        $original = $username;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $original . '.' . $counter;
            $counter++;
        }
        
        return $username;
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
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'role' => ['required_without:role_id', 'string', Rule::in(['capitan', 'guardia'])],
            'guardia_id' => ['nullable', 'exists:guardias,id'],
            'role_id' => ['required_without:role', 'nullable', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ], [
            'role.required_without' => 'Debe seleccionar un Perfil de Sistema o un Rol Personalizado',
            'role_id.required_without' => 'Debe seleccionar un Perfil de Sistema o un Rol Personalizado',
        ]);

        if (!empty($validated['role']) && !empty($validated['role_id'])) {
            return back()->withErrors([
                'role' => 'No puede seleccionar Perfil de Sistema y Rol Personalizado simultáneamente',
                'role_id' => 'No puede seleccionar Perfil de Sistema y Rol Personalizado simultáneamente',
            ])->withInput();
        }

        $username = $validated['username'] ?? $this->generateUsername($validated['name']);
        $username = $this->ensureUniqueUsernameForUpdate($username, $user->id);

        // Determinar role de sistema: si viene role, usarlo; si viene role_id, usar 'guardia'
        $systemRole = $validated['role'] ?? ($validated['role_id'] ? 'guardia' : null);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $username,
            'role' => $systemRole,
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
