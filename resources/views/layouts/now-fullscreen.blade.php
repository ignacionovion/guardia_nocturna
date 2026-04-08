<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GuardiAPP NOW - ' . branding()->nombre_empresa)</title>
    <link rel="icon" type="image/x-icon" href="{{ branding()->favicon ?? asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Design System --}}
    @include('components.design-system')
    
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        @keyframes pulse-live {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            50% { opacity: 0.8; box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        }
        .pulse-live { animation: pulse-live 2s ease-in-out infinite; }
        
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.3s ease-out; }
        
        .glass { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        
        .grid-pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(148, 163, 184, 0.15) 1px, transparent 0);
            background-size: 24px 24px;
        }
        .dark .grid-pattern {
            background-image: radial-gradient(circle at 1px 1px, rgba(71, 85, 105, 0.3) 1px, transparent 0);
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-white dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased min-h-screen">
    @include('components.impersonation-banner')
    
    <div class="min-h-screen flex flex-col">
        {{-- Topbar Operativa --}}
        <header class="sticky top-0 z-50 h-14 flex items-center justify-between px-4 sm:px-6 bg-white/80 dark:bg-slate-900/80 glass border-b border-slate-200 dark:border-slate-800">
            {{-- Left: Branding + Status --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group" title="Volver al Dashboard">
                    @if(branding()->logo)
                        <img src="{{ branding()->logo }}" alt="{{ branding()->nombre_empresa }}" class="h-8 w-auto">
                    @else
                        <div class="w-9 h-9 bg-gradient-to-br from-red-500 to-red-700 rounded-xl flex items-center justify-center shadow-lg shadow-red-500/20">
                            <i class="fas fa-bolt text-white text-sm"></i>
                        </div>
                    @endif
                </a>
                
                <div class="hidden sm:flex items-center gap-3">
                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 pulse-live" id="header-live-indicator"></div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">GuardiAPP NOW</span>
                    </div>
                </div>
            </div>
            
            {{-- Center: Live Status --}}
            <div class="hidden md:flex items-center gap-4">
                @hasSection('header-center')
                    @yield('header-center')
                @endif
            </div>
            
            {{-- Right: Actions --}}
            <div class="flex items-center gap-2">
                {{-- Clock --}}
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <i class="fas fa-clock text-slate-400 text-xs"></i>
                    <span id="live-clock" class="text-sm font-mono font-semibold text-slate-700 dark:text-slate-300">--:--:--</span>
                </div>
                
                {{-- Theme Toggle --}}
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800 transition-colors"
                        title="Cambiar tema">
                    <i class="fas text-sm" :class="darkMode ? 'fa-sun text-amber-400' : 'fa-moon'"></i>
                </button>
                
                {{-- Fullscreen Toggle --}}
                <button onclick="toggleFullscreen()" 
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800 transition-colors"
                        title="Pantalla completa">
                    <i class="fas fa-expand text-sm"></i>
                </button>
                
                {{-- Back to Dashboard --}}
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800 transition-colors">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span class="hidden sm:inline">Dashboard</span>
                </a>
            </div>
        </header>
        
        {{-- Main Content --}}
        <main class="flex-1 grid-pattern">
            <div class="h-full p-4 sm:p-6 lg:p-8">
                @yield('content')
            </div>
        </main>
        
        {{-- Minimal Footer --}}
        <footer class="px-4 sm:px-6 py-2 border-t border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 glass">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-500">
                <div class="flex items-center gap-2">
                    <i class="fas fa-bolt text-red-500"></i>
                    <span class="font-medium">{{ branding()->nombre_empresa }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <span id="footer-last-update" class="text-slate-400">—</span>
                    <span>&copy; {{ date('Y') }} GuardiAPP</span>
                </div>
            </div>
        </footer>
    </div>
    
    <script>
        // Live clock
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('live-clock');
            if (el) el.textContent = `${h}:${m}:${s}`;
        }
        updateClock();
        setInterval(updateClock, 1000);
        
        // Fullscreen toggle
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen().catch(() => {});
            }
        }
    </script>
    
    @stack('scripts')
</body>
</html>
