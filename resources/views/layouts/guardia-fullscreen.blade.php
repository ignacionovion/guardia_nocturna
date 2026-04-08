<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Guardia - ' . branding()->nombre_empresa)</title>
    <link rel="icon" type="image/x-icon" href="{{ branding()->favicon ?? asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    {{-- Inter Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        /* Animations */
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-0.5rem); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-pulse-slow { animation: pulse-slow 2s ease-in-out infinite; }
        .animate-slide-in { animation: slideIn 0.3s ease-out; }
        
        /* Status indicator pulse */
        .status-pulse {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: status-pulse 2s infinite;
        }
        @keyframes status-pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        
        /* Glass effect */
        .glass { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    </style>
    
    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 antialiased min-h-screen">
    @include('components.impersonation-banner')
    
    <div class="min-h-screen flex flex-col">
        {{-- Minimal Topbar --}}
        <header class="sticky top-0 z-40 flex items-center justify-between h-14 px-4 sm:px-6 bg-slate-900/80 glass border-b border-slate-800">
            {{-- Left: Logo + Title --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    @if(branding()->logo)
                        <img src="{{ branding()->logo }}" alt="{{ branding()->nombre_empresa }}" class="h-8 w-auto">
                    @else
                        <div class="w-8 h-8 bg-gradient-to-br from-red-500 to-red-700 rounded-lg flex items-center justify-center shadow-lg shadow-red-500/20">
                            <i class="fas fa-helmet-safety text-white text-xs"></i>
                        </div>
                    @endif
                </a>
                <div class="hidden sm:block h-6 w-px bg-slate-700"></div>
                <div class="hidden sm:flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 status-pulse"></span>
                    <span class="text-sm font-semibold text-white">@yield('panel-title', 'Panel de Guardia')</span>
                </div>
            </div>
            
            {{-- Center: Status --}}
            <div class="hidden md:flex items-center gap-3">
                @hasSection('status-bar')
                    @yield('status-bar')
                @endif
            </div>
            
            {{-- Right: Actions --}}
            <div class="flex items-center gap-2">
                {{-- Time --}}
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-700">
                    <i class="fas fa-clock text-slate-400 text-xs"></i>
                    <span id="live-time" class="text-sm font-mono font-medium text-white">--:--</span>
                </div>
                
                {{-- Theme toggle --}}
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                        class="p-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors"
                        title="Cambiar tema">
                    <i class="fas text-sm" :class="darkMode ? 'fa-sun text-amber-400' : 'fa-moon'"></i>
                </button>
                
                {{-- Back to Dashboard --}}
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span class="hidden sm:inline">Volver</span>
                </a>
                
                {{-- User Menu --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-800 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                    </button>
                    
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-xl shadow-2xl border border-slate-700 overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-slate-700">
                            <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Usuario' }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                        </div>
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                    <i class="fas fa-sign-out-alt w-4"></i>
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        {{-- Main Content - Full Width --}}
        <main class="flex-1 p-4 sm:p-6">
            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="mb-4 animate-slide-in">
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-900/30 border border-emerald-700/50">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center">
                        <i class="fas fa-check text-white text-sm"></i>
                    </div>
                    <p class="text-sm font-medium text-emerald-200">{{ session('success') }}</p>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-emerald-400 hover:text-emerald-200">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            </div>
            @endif
            
            @if(session('error'))
            <div class="mb-4 animate-slide-in">
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-red-900/30 border border-red-700/50">
                    <div class="w-8 h-8 rounded-lg bg-red-500 flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                    </div>
                    <p class="text-sm font-medium text-red-200">{{ session('error') }}</p>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-200">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            </div>
            @endif
            
            @yield('content')
        </main>
        
        {{-- Minimal Footer --}}
        <footer class="px-4 sm:px-6 py-2 border-t border-slate-800 bg-slate-900/50">
            <div class="flex items-center justify-between text-xs text-slate-500">
                <div class="flex items-center gap-1.5">
                    <i class="fas fa-helmet-safety text-red-600"></i>
                    <span>{{ branding()->nombre_empresa }}</span>
                </div>
                <div>&copy; {{ date('Y') }} GuardiAPP</div>
            </div>
        </footer>
    </div>
    
    {{-- Live Time Script --}}
    <script>
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('live-time');
            if (el) el.textContent = `${hours}:${minutes}:${seconds}`;
        }
        updateTime();
        setInterval(updateTime, 1000);
    </script>
    
    @stack('scripts')
</body>
</html>
