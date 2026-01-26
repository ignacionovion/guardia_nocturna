<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia Nocturna</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 font-sans leading-normal tracking-normal flex flex-col min-h-screen text-slate-800">

    <nav class="bg-slate-900 shadow-xl border-b-4 border-red-700 sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo / Marca -->
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                    <div class="bg-red-700 p-2 rounded-lg text-white transform group-hover:rotate-3 transition-transform duration-300 shadow-lg border border-red-600">
                        <i class="fas fa-helmet-safety text-lg"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-white text-lg font-bold tracking-wide uppercase leading-none">Guardia Nocturna</span>
                        <span class="text-slate-400 text-xs font-medium tracking-wider uppercase">Sistema de Gestión</span>
                    </div>
                </a>

                <!-- Menú de Navegación -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fas fa-home mr-1.5 opacity-70"></i> Inicio
                    </a>
                    <a href="{{ route('camas') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('camas') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fas fa-bed mr-1.5 opacity-70"></i> Camas
                    </a>
                    
                    @if(Auth::user()->role === 'guardia')
                        <a href="{{ route('admin.dotaciones') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.dotaciones*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-users-gear mr-1.5 opacity-70"></i> Mi Dotación
                        </a>
                    @endif
                    
                    @if(Auth::user()->role === 'super_admin')
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
                        <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.reports*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-chart-pie mr-1.5 text-red-400"></i> Reportes
                        </a>
                    @endif
                </div>

                <!-- Perfil de Usuario -->
                @auth
                    <div class="flex items-center pl-4 ml-4 border-l border-slate-700">
                        <div class="flex flex-col items-end mr-3">
                            <span class="text-slate-200 text-sm font-semibold leading-tight">{{ Auth::user()->name }}</span>
                            <span class="text-slate-500 text-xs uppercase">{{ str_replace('_', ' ', Auth::user()->role) }}</span>
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
    </nav>

    <div class="container mx-auto mt-4 px-4 pb-6 flex-grow">
        <!-- Alertas Globales -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex justify-between items-center" role="alert">
                <div>
                    <p class="font-bold">¡Operación exitosa!</p>
                    <p>{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900"><i class="fas fa-times"></i></button>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex justify-between items-center" role="alert">
                <div>
                    <p class="font-bold">Se encontraron errores:</p>
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900"><i class="fas fa-times"></i></button>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 mt-auto">
        <div class="container mx-auto py-8 px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2 mb-4 md:mb-0">
                    <i class="fas fa-helmet-safety text-red-700 text-xl"></i>
                    <span class="font-bold text-slate-200 tracking-wide">GUARDIA NOCTURNA</span>
                </div>
                <div class="text-sm">
                    &copy; {{ date('Y') }} Sistema de Gestión de Cuerpo de Bomberos. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
