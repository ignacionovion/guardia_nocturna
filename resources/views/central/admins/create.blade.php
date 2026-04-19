@extends('central.layouts.app')

@section('title', 'Nuevo administrador')

@section('content')
    <div class="mb-8">
        <a href="{{ Route::has('central.admins.index') ? route('central.admins.index') : url('/admin/admins') }}" class="text-sm text-slate-500 hover:text-slate-800">← Volver al listado</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-2">Nuevo administrador SaaS</h1>
        <p class="text-slate-500 text-sm mt-1">Credencial de acceso: nombre de usuario y contraseña</p>
    </div>

    <div class="max-w-xl bg-white rounded-2xl border border-slate-200 p-6">
        <form method="POST" action="{{ Route::has('central.admins.store') ? route('central.admins.store') : url('/admin/admins') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-xs font-medium text-slate-500 mb-1">Nombre completo</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none @error('name') border-red-400 @enderror">
                @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="username" class="block text-xs font-medium text-slate-500 mb-1">Usuario (login)</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" required autocomplete="username"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none @error('username') border-red-400 @enderror">
                @error('username')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-medium text-slate-500 mb-1">Correo (opcional, administrativo)</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="email"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none @error('email') border-red-400 @enderror">
                @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-slate-500 mb-1">Contraseña</label>
                <input type="password" name="password" id="password" required autocomplete="new-password"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none @error('password') border-red-400 @enderror">
                @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-medium text-slate-500 mb-1">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" id="activo" value="1" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500" @checked(old('activo', true))>
                <label for="activo" class="text-sm text-slate-700">Cuenta activa</label>
            </div>
            @error('activo')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_super_admin" value="0">
                <input type="checkbox" name="is_super_admin" id="is_super_admin" value="1" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500" @checked(old('is_super_admin'))>
                <label for="is_super_admin" class="text-sm text-slate-700">Super administrador (gestiona otros administradores)</label>
            </div>
            @error('is_super_admin')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror

            <div class="pt-2">
                <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white text-sm font-medium rounded-xl hover:bg-slate-800 transition">
                    Crear administrador
                </button>
            </div>
        </form>
    </div>
@endsection
