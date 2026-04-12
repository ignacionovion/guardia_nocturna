@extends('layouts.tenant-auth')

@section('title', 'Nueva contraseña - '.config('app.name', 'EstacionAPP'))

@section('content')
<div class="tenant-auth-shell">
    <div class="tenant-auth-wrap">
        <div class="tenant-auth-card">
            <div class="tenant-auth-body">
                <h1 class="tenant-auth-title">Definir nueva contraseña</h1>
                <p class="tenant-auth-subtitle">Por seguridad debes cambiar la contraseña temporal antes de continuar.</p>

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.initial.update') }}" class="tenant-auth-form">
                    @csrf
                    <div class="tenant-auth-field">
                        <label for="password">Nueva contraseña</label>
                        <input type="password" name="password" id="password" required autocomplete="new-password">
                    </div>
                    <div class="tenant-auth-field">
                        <label for="password_confirmation">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="tenant-auth-submit">Guardar y continuar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
