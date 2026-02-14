<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AppGuardia') }} - Sistema de Gestión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex flex-col items-center justify-center relative overflow-hidden">

    <!-- Fondo Decorativo -->
    <div class="absolute inset-0 z-0 opacity-10">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-red-600 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-600 rounded-full blur-3xl"></div>
    </div>

    <!-- Contenido Principal -->
    <div class="z-10 text-center px-4 max-w-3xl">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-slate-800 text-white mb-8 border-4 border-slate-700 shadow-2xl animate-fade-in-down">
            <i class="fas fa-helmet-safety text-5xl text-red-600"></i>
        </div>
        
        <h1 class="text-5xl md:text-6xl font-extrabold text-white tracking-tight uppercase mb-4 drop-shadow-lg">
            {{ config('app.name', 'AppGuardia') }}
        </h1>
        
        <p class="text-lg md:text-xl text-slate-400 mb-10 font-light tracking-wide max-w-xl mx-auto">
            Sistema integral de gestión de turnos, asistencia y control operativo para Cuerpos de Bomberos.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto px-8 py-4 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-red-900/30 transform hover:-translate-y-1 flex items-center justify-center text-lg uppercase tracking-wide">
                        <i class="fas fa-gauge-high mr-3"></i> Ir al Panel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-slate-100 text-slate-900 font-bold rounded-xl transition-all shadow-lg hover:shadow-white/10 transform hover:-translate-y-1 flex items-center justify-center text-lg uppercase tracking-wide">
                        <i class="fas fa-right-to-bracket mr-3 text-red-700"></i> Ingresar al Sistema
                    </a>
                @endauth
            @endif
        </div>
        
        <!-- Stats Rápidos (Visual) -->
        <div class="mt-16 grid grid-cols-2 md:grid-cols-3 gap-8 text-center border-t border-slate-800 pt-8 opacity-75">
            <div>
                <span class="block text-2xl font-bold text-white mb-1"><i class="fas fa-shield-halved text-red-500"></i></span>
                <span class="text-xs text-slate-500 uppercase font-bold tracking-wider">Gestión Turnos</span>
            </div>
            <div>
                <span class="block text-2xl font-bold text-white mb-1"><i class="fas fa-users text-blue-500"></i></span>
                <span class="text-xs text-slate-500 uppercase font-bold tracking-wider">Control Personal</span>
            </div>
            <div class="hidden md:block">
                <span class="block text-2xl font-bold text-white mb-1"><i class="fas fa-chart-line text-yellow-500"></i></span>
                <span class="text-xs text-slate-500 uppercase font-bold tracking-wider">Reportes</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="absolute bottom-6 w-full text-center z-10">
        <p class="text-slate-600 text-xs uppercase tracking-widest font-semibold">
            &copy; {{ date('Y') }} {{ config('app.name', 'AppGuardia') }}
        </p>
    </div>

</body>
</html>
