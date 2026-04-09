<!DOCTYPE html>
<html lang="es" x-data="{ 
    sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
    mobileSidebarOpen: false
}" x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', branding()->nombre_empresa)</title>
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
    
    {{-- Custom Styles --}}
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', system-ui, sans-serif; }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #dbe4ee; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        .dark ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        /* Animations */
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(1rem); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes pulse-slow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.015); opacity: 0.9; }
        }
        .animate-slide-in { animation: slideIn 0.3s ease-out; }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
        .animate-fade-in { animation: fadeIn 0.2s ease-out; }
        .animate-pulse-slow { animation: pulse-slow 2s ease-in-out infinite; }
        
        /* Glass effect */
        .glass { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
        
        /* Gradient text */
        .gradient-text { 
            background: linear-gradient(135deg, #dc2626 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Status indicator pulse */
        .status-pulse {
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: status-pulse 2s infinite;
        }
        @keyframes status-pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        
        /* Grid Pattern Background (estilo NOW) */
        .grid-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(circle at 1px 1px, rgba(148, 163, 184, 0.15) 1px, transparent 0);
            background-size: 24px 24px;
        }
        
        /* Glass Effect (estilo NOW) */
        .glass {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        
        @keyframes slide-in {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
        @keyframes status-pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    </style>
    
    @stack('styles')
</head>
<body class="admin-panel grid-pattern text-slate-900 antialiased">
    @include('components.impersonation-banner')
    
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        @auth
            {{-- Desktop Sidebar --}}
            <div class="hidden lg:block" :class="sidebarCollapsed ? 'w-16' : 'w-64'">
                @include('components.layouts.sidebar')
            </div>
            
            {{-- Mobile Sidebar Overlay --}}
            <div x-show="mobileSidebarOpen" 
                 x-transition:enter="transition-opacity ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileSidebarOpen = false"
                 class="lg:hidden fixed inset-0 z-40 bg-black/50"></div>
            
            {{-- Mobile Sidebar --}}
            <div x-show="mobileSidebarOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="lg:hidden fixed inset-y-0 left-0 z-50 w-64">
                @include('components.layouts.sidebar')
            </div>
        @endauth
        
        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0" :class="{ 'lg:ml-0': !$refs }">
            {{-- Topbar --}}
            @auth
                @include('components.layouts.topbar', ['title' => $__env->yieldContent('page-title', '')])
            @endauth
            
            {{-- Page Content --}}
            {{-- min-w-0: flex child puede encoger sin romper anchos; sin overflow-x-hidden aquí para no recortar position:fixed hijos (modales vía @stack('modals')). --}}
            <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8">
                {{-- Breadcrumb --}}
                @hasSection('breadcrumb')
                <nav class="mb-6">
                    @yield('breadcrumb')
                </nav>
                @endif
                
                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="mb-6 animate-slide-in">
                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center">
                            <i class="fas fa-check text-white text-sm"></i>
                        </div>
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-emerald-500 hover:text-emerald-700">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                </div>
                @endif
                
                @if(session('error'))
                <div class="mb-6 animate-slide-in">
                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <div class="w-8 h-8 rounded-lg bg-red-500 flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                        </div>
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ session('error') }}</p>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-red-500 hover:text-red-700">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                </div>
                @endif
                
                @if($errors->any())
                <div class="mb-6 animate-slide-in">
                    <div class="px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-red-500 flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                            </div>
                            <p class="text-sm font-semibold text-red-800 dark:text-red-200">Por favor corrige los siguientes errores:</p>
                        </div>
                        <ul class="ml-11 list-disc text-sm text-red-700 dark:text-red-300 space-y-1">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif
                
                {{-- Main Content --}}
                @yield('content')
            </main>
            
            {{-- Footer --}}
            <footer class="px-4 sm:px-6 py-3 border-t border-[#e5e7eb] bg-white">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[#475569]">
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-helmet-safety text-red-600"></i>
                        <span class="font-medium">{{ branding()->nombre_empresa }}</span>
                    </div>
                    <div>
                        &copy; {{ date('Y') }} GuardiAPP
                    </div>
                </div>
            </footer>
        </div>
    </div>

    {{-- Modales al final de <body>: evitan clip/stacking raro por overflow en main y aseguran ancho viewport --}}
    @stack('modals')
    
    {{-- Save sidebar state --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.effect(() => {
                const collapsed = Alpine.store('sidebarCollapsed');
                if (collapsed !== undefined) {
                    localStorage.setItem('sidebarCollapsed', collapsed);
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
