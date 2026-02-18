<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - {{ config('app.name', 'AppGuardia') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 font-sans leading-normal tracking-normal flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md">
        <!-- Logo / Marca Principal -->
        <div class="text-center mb-8">
            @if(file_exists(public_path('brand/guardiapp.png')))
                <img src="{{ asset('brand/guardiapp.png') }}" alt="GuardiaAPP" class="mx-auto mb-1 h-[280px] w-auto">
            @else
                <div class="inline-flex items-center justify-center w-10 h-20 rounded-full bg-red-300 text-white mb-4 border-4 border-slate-300 shadow-2xl">
                    <i class="fas fa-helmet-safety text-4xl"></i>
                </div>
            @endif
            <p class="text-slate-400 text-sm font-medium tracking-wide uppercase -mt-2">Sistema de Gestión Operativa</p>
        </div>

        <!-- Tarjeta de Login -->
        <div class="bg-white shadow-2xl rounded-xl overflow-hidden border-t-4 border-red-700">
            <div class="px-8 py-10">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-800">Bienvenido</h2>
                    <p class="text-slate-500 text-sm">Ingrese sus credenciales para acceder al sistema.</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-5">
                        <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide" for="username">
                            Usuario
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-slate-400"></i>
                            </div>
                            <input class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all @error('username') border-red-500 bg-red-50 @enderror" 
                                   id="username" type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="admin">
                        </div>
                        @error('username')
                            <p class="text-red-600 text-xs mt-1 font-medium flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-8">
                        <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide" for="password">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-slate-400"></i>
                            </div>
                            <input class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all @error('password') border-red-500 bg-red-50 @enderror" 
                                   id="password" type="password" name="password" required placeholder="••••••••">
                        </div>
                        @error('password')
                            <p class="text-red-600 text-xs mt-1 font-medium flex items-center"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <button class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5 flex items-center justify-center uppercase tracking-wide text-sm" type="submit">
                        <i class="fas fa-right-to-bracket mr-2"></i> Iniciar Sesión
                    </button>
                </form>
            </div>
            <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'AppGuardia') }}.
                </p>
            </div>
        </div>
    </div>

</body>
</html>
