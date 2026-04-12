@extends('layouts.tenant-auth')

@section('title', 'Nueva contraseña - '.config('app.name', 'EstacionAPP'))

@section('content')
    <div class="tenant-auth-shell">
        <div class="tenant-auth-wrap">
            <div class="tenant-auth-brand">
                @if(file_exists(public_path('brand/guardiapp.png')))
                    <img
                        src="{{ asset('brand/guardiapp.png') }}?v={{ filemtime(public_path('brand/guardiapp.png')) }}"
                        alt="GuardiAPP"
                        class="tenant-auth-brand-logo"
                    >
                @else
                    <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-700 shadow-lg shadow-red-500/25">
                        <i class="fas fa-helmet-safety text-3xl text-white"></i>
                    </div>
                @endif
                <p class="tenant-auth-badge">Sistema de Gestion Operativa</p>
            </div>

            <div class="tenant-auth-card">
                <div class="tenant-auth-body">
                    <h1 class="tenant-auth-title">Definir nueva contraseña</h1>
                    <p class="tenant-auth-subtitle">Por seguridad debes cambiar la contraseña temporal antes de continuar.</p>

                    <form method="POST" action="{{ route('password.initial.update') }}" class="tenant-auth-form">
                        @csrf

                        <div class="tenant-auth-field">
                            <label for="password" class="tenant-auth-label">Nueva contraseña</label>
                            <div class="tenant-auth-input-wrap">
                                <i class="fas fa-lock tenant-auth-icon" aria-hidden="true"></i>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                    class="tenant-auth-input @error('password') tenant-auth-input-error @enderror"
                                >
                            </div>
                            @error('password')
                                <p class="tenant-auth-error"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="tenant-auth-field">
                            <label for="password_confirmation" class="tenant-auth-label">Confirmar contraseña</label>
                            <div class="tenant-auth-input-wrap">
                                <i class="fas fa-lock tenant-auth-icon" aria-hidden="true"></i>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Repite la contraseña"
                                    class="tenant-auth-input @error('password_confirmation') tenant-auth-input-error @enderror"
                                >
                            </div>
                            @error('password_confirmation')
                                <p class="tenant-auth-error"><i class="fas fa-circle-exclamation mr-1"></i>{{ $message }}</p>
                            @enderror
                        </div>

                        <button class="tenant-auth-submit" type="submit">
                            <i class="fas fa-check" aria-hidden="true"></i>
                            <span>Guardar y continuar</span>
                        </button>
                    </form>
                </div>

                <div class="tenant-auth-footer">
                    &copy; {{ date('Y') }} {{ config('app.name', 'EstacionAPP') }}
                </div>
            </div>
        </div>
    </div>
@endsection
