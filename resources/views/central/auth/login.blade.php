@extends('layouts.central-auth')

@section('title', 'Acceso — Panel Central')

@section('content')
    <div class="central-auth-shell">
        <div class="central-auth-wrap">
            <div class="central-auth-brand">
                <div class="central-auth-brand-mark" aria-hidden="true">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                    </svg>
                </div>
                <h1 class="central-auth-title">Panel Central</h1>
                <p class="central-auth-subtitle">Administración de plataforma SaaS</p>
                <span class="central-auth-badge">EstacionAPP</span>
            </div>

            <div class="central-auth-card">
                <div class="central-auth-body">
                    <form method="POST" action="{{ url()->current() }}" class="central-auth-form">
                        @csrf

                        <div class="central-auth-field">
                            <label for="username" class="central-auth-label">Usuario</label>
                            <div class="central-auth-input-wrap">
                                <i class="fas fa-user central-auth-icon" aria-hidden="true"></i>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    value="{{ old('username') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="Usuario"
                                    class="central-auth-input @error('username') central-auth-input-error @enderror"
                                >
                            </div>
                            @error('username')
                                <p class="central-auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="central-auth-field">
                            <label for="password" class="central-auth-label">Contraseña</label>
                            <div class="central-auth-input-wrap">
                                <i class="fas fa-lock central-auth-icon" aria-hidden="true"></i>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="central-auth-input @error('password') central-auth-input-error @enderror"
                                >
                            </div>
                            @error('password')
                                <p class="central-auth-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="central-auth-remember">
                            <input type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>
                            <label for="remember">Recordarme</label>
                        </div>

                        <button type="submit" class="central-auth-submit">
                            <i class="fas fa-right-to-bracket" aria-hidden="true"></i>
                            <span>Iniciar sesión</span>
                        </button>
                    </form>
                </div>

                <div class="central-auth-footer">
                    © {{ date('Y') }} {{ config('app.name', 'EstacionAPP') }} — Panel central
                </div>
            </div>
        </div>
    </div>
@endsection
