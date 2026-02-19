<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AppGuardia') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-b from-slate-100 via-slate-50 to-slate-100 font-sans leading-normal tracking-normal flex flex-col min-h-screen text-slate-800">

    @if(!(Auth::check() && Auth::user()->role === 'guardia' && request()->routeIs('dashboard')))
    <nav class="bg-slate-900 shadow-xl border-b-4 border-red-700 sticky top-0 z-50 pt-[env(safe-area-inset-top)]">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo / Marca -->
                <a href="{{ route('dashboard') }}" class="flex items-center group">
                    @if(file_exists(public_path('brand/guardiapp.png')))
                        <img src="{{ asset('brand/guardiapp.png') }}?v={{ filemtime(public_path('brand/guardiapp.png')) }}" alt="GuardiaAPP" class="h-14 w-auto drop-shadow-sm">
                    @else
                        <div class="bg-red-300 p-2.5 rounded-lg text-white transform group-hover:rotate-3 transition-transform duration-300 shadow-lg border border-red-200">
                            <i class="fas fa-helmet-safety text-xl"></i>
                        </div>
                    @endif
                </a>

                <!-- Menú de Navegación (Desktop) -->
                <div class="hidden md:flex items-center space-x-1">
                    @auth
                    @if(Auth::user()->role === 'guardia')
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-home mr-1.5 opacity-70"></i> Inicio
                        </a>
                        <a href="{{ route('camas') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('camas') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-bed mr-1.5 opacity-70"></i> Camas
                        </a>
                        <a href="{{ route('admin.dotaciones') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.dotaciones*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-users-gear mr-1.5 opacity-70"></i> Mi Dotación
                        </a>
                    @elseif(Auth::user()->role === 'inventario')
                        <a href="{{ route('inventario.index') }}" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('inventario.*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-boxes-stacked mr-1.5 opacity-80"></i> Inventario
                        </a>
                    @elseif(Auth::user()->role === 'super_admin')
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-home mr-1.5 opacity-80"></i> Inicio
                        </a>
                        <div class="h-6 w-px bg-slate-700 mx-2"></div>

                        <div class="relative group">
                            <button type="button" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors text-slate-200 hover:bg-slate-800 hover:text-white">
                                <i class="fas fa-layer-group mr-1.5 opacity-80"></i>
                                Gestión
                                <i class="fas fa-chevron-down ml-1 text-[10px] opacity-70"></i>
                            </button>
                            <div class="hidden group-hover:block absolute left-0 top-full w-56 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                <div class="h-2"></div>
                                <a href="{{ route('admin.volunteers.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-users mr-2 text-slate-500"></i> Voluntarios
                                </a>
                                <a href="{{ route('admin.emergencies.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-truck-medical mr-2 text-slate-500"></i> Emergencias
                                </a>
                                <a href="{{ route('admin.calendario') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-calendar-alt mr-2 text-slate-500"></i> Calendario
                                </a>
                            </div>
                        </div>

                        <div class="relative group">
                            <button type="button" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors text-slate-200 hover:bg-slate-800 hover:text-white">
                                <i class="fas fa-shield-halved mr-1.5 opacity-80"></i>
                                Guardias
                                <i class="fas fa-chevron-down ml-1 text-[10px] opacity-70"></i>
                            </button>
                            <div class="hidden group-hover:block absolute left-0 top-full w-56 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                <div class="h-2"></div>
                                <a href="{{ route('guardia.now') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-bolt mr-2 text-slate-500"></i> Now
                                </a>
                                <a href="{{ route('admin.guardias') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-shield mr-2 text-slate-500"></i> Guardias
                                </a>
                                <a href="{{ route('admin.dotaciones') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-users-gear mr-2 text-slate-500"></i> Dotaciones
                                </a>
                                <a href="{{ route('camas') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-bed mr-2 text-slate-500"></i> Camas
                                </a>
                                <a href="{{ route('admin.reports.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-chart-pie mr-2 text-slate-500"></i> Reportes
                                </a>
                            </div>
                        </div>

                        <div class="relative group">
                            <button type="button" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('admin.preventivas*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-clipboard-list mr-1.5 opacity-80"></i>
                                Preventivas
                                <i class="fas fa-chevron-down ml-1 text-[10px] opacity-70"></i>
                            </button>
                            <div class="hidden group-hover:block absolute left-0 top-full w-56 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                <div class="h-2"></div>
                                <a href="{{ route('admin.preventivas.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-list mr-2 text-slate-500"></i> Eventos
                                </a>
                                <div class="px-4 py-2 text-[10px] font-black text-slate-400 uppercase tracking-wider">Reportes por evento</div>
                                <div class="px-4 pb-2 text-xs text-slate-500">Ver reporte desde el detalle de cada evento</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.planillas.index') }}" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('admin.planillas*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-table-list mr-1.5 opacity-80"></i> Planillas
                        </a>
                        <a href="{{ route('inventario.index') }}" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('inventario.*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-boxes-stacked mr-1.5 opacity-80"></i> Inventario
                        </a>
                    @elseif(Auth::user()->role === 'capitania')
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-home mr-1.5 opacity-70"></i> Inicio
                        </a>
                        <a href="{{ route('camas') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('camas') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-bed mr-1.5 opacity-70"></i> Camas
                        </a>
                    @endif
                    
                    @if(in_array(Auth::user()->role, ['super_admin', 'capitania'], true) && Auth::user()->role !== 'super_admin')
                        <div class="h-6 w-px bg-slate-700 mx-2"></div>
                        
                        <a href="{{ route('admin.guardias') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.guardias*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-shield mr-1.5 text-red-400"></i> Guardias
                        </a>
                        <a href="{{ route('admin.dotaciones') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.dotaciones*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-users-gear mr-1.5 text-red-400"></i> Dotaciones
                        </a>
                        <a href="{{ route('admin.calendario') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.calendario*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-calendar-alt mr-1.5 text-red-400"></i> Calendario
                        </a>
                        <a href="{{ route('admin.volunteers.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.volunteers*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-users mr-1.5 text-red-400"></i> Voluntarios
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.users*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-user-shield mr-1.5 text-red-400"></i> Usuarios
                        </a>
                        <a href="{{ route('admin.emergencies.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.emergencies*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-truck-medical mr-1.5 text-red-400"></i> Emergencias
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.reports*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-chart-pie mr-1.5 text-red-400"></i> Reportes
                        </a>
                    @endif
                    @endauth
                </div>

                <!-- Controles Mobile + Perfil -->
                <div class="flex items-center gap-2">
                    <!-- Botón Menú (Mobile) -->
                    <button type="button" id="mobile-menu-button" class="md:hidden shrink-0 bg-slate-800 text-slate-200 p-2 rounded-lg transition-all duration-200 border border-slate-700" aria-label="Abrir menú">
                        <i class="fas fa-bars"></i>
                    </button>

                    <!-- Perfil de Usuario -->
                    @auth
                        <div class="flex items-center pl-3 ml-1 border-l border-slate-700">
                            <div class="relative hidden sm:block mr-3" id="user-menu-root">
                                <button type="button" id="user-menu-button" class="flex flex-col items-end rounded-lg px-2 py-1 hover:bg-slate-800 transition-colors">
                                    <span class="text-slate-200 text-sm font-semibold leading-tight">{{ Auth::user()->name }}</span>
                                    <span class="text-slate-500 text-xs uppercase">{{ str_replace('_', ' ', Auth::user()->role) }}</span>
                                </button>
                                @if(Auth::user()->role === 'super_admin')
                                    <div id="user-menu-dropdown" class="hidden absolute right-0 top-full w-64 pt-2">
                                        <div class="bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                            <div class="py-2">
                                                <a href="{{ route('admin.system.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                                    <i class="fas fa-gear mr-2 text-slate-500"></i> Administración del Sistema
                                                </a>
                                                <a href="{{ route('admin.users.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                                    <i class="fas fa-user-shield mr-2 text-slate-500"></i> Usuarios
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="bg-slate-800 hover:bg-red-900 text-slate-300 hover:text-white p-2 rounded-lg transition-all duration-200 border border-slate-700 hover:border-red-700" title="Cerrar Sesión">
                                    <i class="fas fa-right-from-bracket"></i>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Menú Mobile -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="mt-3 rounded-2xl border border-slate-800 bg-slate-950/30 overflow-hidden">
                    <div class="p-2 space-y-1">
                        @auth
                            @if(Auth::user()->role === 'guardia')
                                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-home mr-2 opacity-80"></i> Inicio
                                </a>
                                <a href="{{ route('camas') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('camas') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-bed mr-2 opacity-80"></i> Camas
                                </a>
                                <a href="{{ route('admin.dotaciones') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('admin.dotaciones*') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-users-gear mr-2 opacity-80"></i> Mi Dotación
                                </a>
                            @elseif(Auth::user()->role === 'inventario')
                                <a href="{{ route('inventario.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('inventario.*') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-boxes-stacked mr-2 opacity-80"></i> Inventario
                                </a>
                                <a href="{{ route('admin.volunteers.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-users mr-2 opacity-80"></i> Voluntarios
                                </a>
                                <a href="{{ route('admin.emergencies.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-truck-medical mr-2 opacity-80"></i> Emergencias
                                </a>
                            @elseif(Auth::user()->role === 'super_admin')
                                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-home mr-2 opacity-80"></i> Inicio
                                </a>
                                <a href="{{ route('guardia.now') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('guardia.now*') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-bolt mr-2 opacity-80"></i> Now
                                </a>
                                <a href="{{ route('admin.volunteers.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-users mr-2 opacity-80"></i> Voluntarios
                                </a>
                                <a href="{{ route('admin.emergencies.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-truck-medical mr-2 opacity-80"></i> Emergencias
                                </a>
                                <a href="{{ route('admin.calendario') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-calendar-alt mr-2 opacity-80"></i> Calendario
                                </a>
                                <a href="{{ route('admin.guardias') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-shield mr-2 opacity-80"></i> Guardias
                                </a>
                                <a href="{{ route('admin.dotaciones') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-users-gear mr-2 opacity-80"></i> Dotaciones
                                </a>
                                <a href="{{ route('camas') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-bed mr-2 opacity-80"></i> Camas
                                </a>
                                <a href="{{ route('admin.system.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-gear mr-2 opacity-80"></i> Administración del Sistema
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-user-shield mr-2 opacity-80"></i> Usuarios
                                </a>
                                <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-chart-pie mr-2 opacity-80"></i> Reportes
                                </a>
                                <a href="{{ route('admin.preventivas.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-clipboard-list mr-2 opacity-80"></i> Preventivas
                                </a>
                                <a href="{{ route('admin.planillas.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-table-list mr-2 opacity-80"></i> Planillas
                                </a>
                                <a href="{{ route('inventario.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-boxes-stacked mr-2 opacity-80"></i> Inventario
                                </a>
                            @elseif(Auth::user()->role === 'capitania')
                                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-home mr-2 opacity-80"></i> Inicio
                                </a>
                                <a href="{{ route('camas') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('camas') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-bed mr-2 opacity-80"></i> Camas
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <script>
        (function () {
            const btn = document.getElementById('mobile-menu-button');
            const menu = document.getElementById('mobile-menu');
            if (!btn || !menu) return;

            btn.addEventListener('click', function () {
                menu.classList.toggle('hidden');
            });
        })();

        (function () {
            const root = document.getElementById('user-menu-root');
            const btn = document.getElementById('user-menu-button');
            const dd = document.getElementById('user-menu-dropdown');
            if (!root || !btn || !dd) return;

            function close() {
                dd.classList.add('hidden');
            }

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dd.classList.toggle('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!root.contains(e.target)) close();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') close();
            });
        })();
    </script>
    @endif

    <div class="{{ Auth::check() && Auth::user()->role === 'guardia' && request()->routeIs('dashboard') ? 'w-full flex-grow' : 'container mx-auto mt-0 px-4 pb-6 pt-6 flex-grow bg-slate-100/80 rounded-3xl border border-slate-200' }}">
        <!-- Alertas Globales -->
        @if(session('success'))
            <div id="global-toast-success" class="fixed top-5 right-5 z-[9999] max-w-md w-[calc(100vw-2.5rem)]">
                <div class="bg-white border border-emerald-200 shadow-2xl rounded-2xl overflow-hidden">
                    <div class="flex items-start gap-3 p-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-black uppercase tracking-widest text-emerald-700">Operación exitosa</div>
                            <div class="text-sm font-semibold text-slate-800 mt-1 break-words">{{ session('success') }}</div>
                        </div>
                        <button type="button" onclick="document.getElementById('global-toast-success')?.remove()" class="text-slate-400 hover:text-slate-700 transition-colors">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                    <div class="h-1 bg-emerald-600"></div>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('global-toast-success');
                    if (el) el.remove();
                }, 4500);
            </script>
        @endif

        @if($errors->any())
            <div id="global-toast-error" class="fixed top-5 right-5 z-[9999] max-w-md w-[calc(100vw-2.5rem)]">
                <div class="bg-white border border-red-200 shadow-2xl rounded-2xl overflow-hidden">
                    <div class="flex items-start gap-3 p-4">
                        <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-black uppercase tracking-widest text-red-700">Atención</div>
                            <ul class="text-sm font-semibold text-slate-800 mt-1 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="break-words">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" onclick="document.getElementById('global-toast-error')?.remove()" class="text-slate-400 hover:text-slate-700 transition-colors">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                    <div class="h-1 bg-red-600"></div>
                </div>
            </div>
        @endif

        @auth
            @php
                $inAppNotifications = \App\Models\InAppNotification::where('user_id', auth()->id())
                    ->whereNull('read_at')
                    ->latest()
                    ->take(3)
                    ->get();
            @endphp

            @if($inAppNotifications->isNotEmpty())
                <div id="inapp-toast-stack" class="fixed top-5 left-1/2 -translate-x-1/2 z-[9998] w-[calc(100vw-2.5rem)] max-w-xl space-y-3">
                    @foreach($inAppNotifications as $n)
                        <div class="bg-white border border-slate-200 shadow-2xl rounded-2xl overflow-hidden">
                            <div class="flex items-start gap-3 p-4">
                                <div class="w-10 h-10 rounded-xl {{ ($n->type === 'guardia') ? 'bg-red-600' : 'bg-indigo-600' }} text-white flex items-center justify-center shrink-0">
                                    <i class="{{ ($n->type === 'guardia') ? 'fas fa-bell' : 'fas fa-chalkboard-user' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-black uppercase tracking-widest text-slate-700">{{ $n->title }}</div>
                                    @if($n->message)
                                        <div class="text-sm font-semibold text-slate-800 mt-1 break-words">{{ $n->message }}</div>
                                    @endif
                                    @if($n->action_url)
                                        <a href="{{ $n->action_url }}" class="inline-flex items-center gap-2 mt-2 text-xs font-black uppercase tracking-widest text-blue-600 hover:text-blue-800">
                                            Ir
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    @endif
                                </div>
                                <button type="button" class="text-slate-400 hover:text-slate-700 transition-colors" onclick="this.closest('.bg-white')?.remove()">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>
                            <div class="h-1 {{ ($n->type === 'guardia') ? 'bg-red-600' : 'bg-indigo-600' }}"></div>
                        </div>
                    @endforeach
                </div>

                <script>
                    (function() {
                        const ids = @json($inAppNotifications->pluck('id'));
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        fetch(@json(route('notifications.read')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ ids })
                        }).catch(() => {});

                        setTimeout(() => {
                            const stack = document.getElementById('inapp-toast-stack');
                            if (stack) stack.remove();
                        }, 6500);
                    })();
                </script>
            @endif
        @endauth

        @yield('content')
    </div>

    @if(!(Auth::check() && Auth::user()->role === 'guardia' && request()->routeIs('dashboard')))
        <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 mt-auto">
            <div class="container mx-auto py-8 px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-2 mb-4 md:mb-0">
                        <i class="fas fa-helmet-safety text-red-700 text-xl"></i>
                        <span class="font-bold text-slate-200 tracking-wide">{{ strtoupper(config('app.name', 'AppGuardia')) }}</span>
                    </div>
                    <div class="text-sm">
                        &copy; {{ date('Y') }} Sistema de Gestión de Cuerpo de Bomberos. Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </footer>
    @endif

    @stack('scripts')
</body>
</html>
