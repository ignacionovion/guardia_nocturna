<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Panel Operativo - ' . branding()->nombre_empresa)</title>
    <link rel="icon" type="image/x-icon" href="{{ branding()->favicon ?? asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    {{-- Inter Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    {{-- Design System --}}
    @include('components.design-system')
    
    <style>
        body { 
            font-family: 'Inter', system-ui, sans-serif;
            overflow-x: hidden;
        }
        
        /* Scrollbar minimalista */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(100, 116, 139, 0.3); border-radius: 2px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(100, 116, 139, 0.5); }
        
        /* Animaciones */
        @keyframes pulse-live {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }
        .animate-pulse-live { animation: pulse-live 2s ease-in-out infinite; }
        
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.3s ease-out; }
        
        /* Fullscreen mode */
        .fullscreen-mode {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9999;
        }
        
        /* Glass effect */
        .glass {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        
        /* Status colors consistentes */
        .status-active { @apply bg-emerald-500; }
        .status-warning { @apply bg-amber-500; }
        .status-danger { @apply bg-red-500; }
        .status-inactive { @apply bg-slate-400; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-slate-950 text-white antialiased min-h-screen">
    {{-- Topbar Operativo Mínimo --}}
    <header class="fixed top-0 left-0 right-0 z-50 glass bg-slate-900/90 border-b border-slate-800">
        <div class="px-4 sm:px-6 h-14 flex items-center justify-between">
            {{-- Logo y Estado --}}
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    @if(branding()->logo_url)
                        <img src="{{ branding()->logo_url }}" alt="{{ branding()->nombre_empresa }}" class="h-8 w-auto">
                    @else
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center shadow-lg shadow-red-500/25">
                            <i class="fas fa-helmet-safety text-white text-sm"></i>
                        </div>
                    @endif
                    <div class="hidden sm:block">
                        <div class="text-sm font-bold text-white">{{ branding()->nombre_empresa }}</div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">Panel Operativo</div>
                    </div>
                </div>
                
                {{-- Indicador de conexión --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/50 border border-slate-700">
                    <span id="connection-dot" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse-live"></span>
                    <span id="connection-text" class="text-xs font-medium text-slate-300">En línea</span>
                </div>
            </div>
            
            {{-- Centro: Reloj --}}
            <div class="absolute left-1/2 transform -translate-x-1/2 hidden md:flex items-center gap-3">
                <div class="text-center">
                    <div id="server-time" class="text-2xl font-bold text-white tabular-nums tracking-tight">--:--:--</div>
                    <div id="server-date" class="text-[10px] text-slate-400 uppercase tracking-wider">Cargando...</div>
                </div>
            </div>
            
            {{-- Acciones --}}
            <div class="flex items-center gap-2">
                @yield('header-actions')
                
                {{-- Toggle Dark Mode --}}
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                        class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-all border border-slate-700">
                    <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
                </button>
                
                {{-- Fullscreen Toggle --}}
                <button onclick="toggleFullscreen()" 
                        class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-all border border-slate-700">
                    <i id="fullscreen-icon" class="fas fa-expand"></i>
                </button>
                
                {{-- Volver al Dashboard --}}
                <a href="{{ url('/dashboard') }}" 
                   class="hidden sm:flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-sm font-medium text-slate-300 hover:text-white transition-all border border-slate-700">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Salir</span>
                </a>
            </div>
        </div>
    </header>
    
    {{-- Contenido Principal --}}
    <main class="pt-14 min-h-screen">
        @yield('content')
    </main>
    
    {{-- Scripts Base --}}
    <script>
        // Reloj en tiempo real
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateStr = now.toLocaleDateString('es-CL', { weekday: 'long', day: 'numeric', month: 'long' });
            
            const timeEl = document.getElementById('server-time');
            const dateEl = document.getElementById('server-date');
            
            if (timeEl) timeEl.textContent = timeStr;
            if (dateEl) dateEl.textContent = dateStr;
        }
        
        setInterval(updateClock, 1000);
        updateClock();
        
        // Fullscreen
        function toggleFullscreen() {
            const icon = document.getElementById('fullscreen-icon');
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().then(() => {
                    icon.classList.remove('fa-expand');
                    icon.classList.add('fa-compress');
                }).catch(err => console.log(err));
            } else {
                document.exitFullscreen().then(() => {
                    icon.classList.remove('fa-compress');
                    icon.classList.add('fa-expand');
                }).catch(err => console.log(err));
            }
        }
        
        document.addEventListener('fullscreenchange', () => {
            const icon = document.getElementById('fullscreen-icon');
            if (document.fullscreenElement) {
                icon.classList.remove('fa-expand');
                icon.classList.add('fa-compress');
            } else {
                icon.classList.remove('fa-compress');
                icon.classList.add('fa-expand');
            }
        });
        
        // Indicador de conexión
        function updateConnectionStatus(online) {
            const dot = document.getElementById('connection-dot');
            const text = document.getElementById('connection-text');
            if (online) {
                dot.classList.remove('bg-red-500');
                dot.classList.add('bg-emerald-500', 'animate-pulse-live');
                text.textContent = 'En línea';
            } else {
                dot.classList.remove('bg-emerald-500', 'animate-pulse-live');
                dot.classList.add('bg-red-500');
                text.textContent = 'Sin conexión';
            }
        }
        
        window.addEventListener('online', () => updateConnectionStatus(true));
        window.addEventListener('offline', () => updateConnectionStatus(false));
    </script>
    
    @stack('scripts')
</body>
</html>
