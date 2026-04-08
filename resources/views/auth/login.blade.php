<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - {{ config('app.name', 'AppGuardia') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 font-sans leading-normal tracking-normal flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md">
        <!-- Logo / Marca Principal -->
        <div class="text-center mb-8">
            @if(file_exists(public_path('brand/guardiapp.png')))
                <img src="{{ asset('brand/guardiapp.png') }}?v={{ filemtime(public_path('brand/guardiapp.png')) }}" alt="GuardiAPP" class="mx-auto mb-1 h-[90px] w-auto">
            @else
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center shadow-lg shadow-red-500/25">
                    <i class="fas fa-helmet-safety text-white text-3xl"></i>
                </div>
            @endif
            <p class="text-xs text-slate-400 uppercase tracking-widest font-semibold">Sistema de Gestión Operativa</p>
        </div>

        <!-- Tarjeta de Login -->
        <div class="bg-white rounded-2xl shadow-2xl border-t-4 border-t-red-600 overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Bienvenido</h2>
                    <p class="text-sm text-slate-500 mt-1">Ingrese sus credenciales para acceder al sistema.</p>
                </div>

                <form method="POST" action="{{ url()->current() }}">
                    @csrf

                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2" for="username">
                            Usuario
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-slate-400"></i>
                            </div>
                            <input class="w-full px-4 py-3 pl-10 bg-white border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all @error('username') !border-red-500 !bg-red-50 @enderror" 
                                   id="username" type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="Ingrese su usuario">
                        </div>
                        @error('username')
                            <p class="mt-1.5 text-xs text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-8">
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2" for="password">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-slate-400"></i>
                            </div>
                            <input class="w-full px-4 py-3 pl-10 bg-white border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all @error('password') !border-red-500 !bg-red-50 @enderror" 
                                   id="password" type="password" name="password" required placeholder="••••••••">
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <button class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-all duration-150 flex items-center justify-center gap-2 shadow-lg shadow-red-500/25 hover:shadow-red-500/40" type="submit">
                        <i class="fas fa-right-to-bracket"></i>
                        <span>Iniciar Sesión</span>
                    </button>
                </form>
            </div>
            <div class="px-6 sm:px-8 py-4 bg-white border-t border-slate-100 text-center">
                <p class="text-xs text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'AppGuardia') }}
                </p>
            </div>
        </div>
    </div>

</body>
</html>
