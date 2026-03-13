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
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        }
                    }
                }
            }
        }
    </script>
    
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
        body { font-family: 'Inter', system-ui, sans-serif; }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }
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
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .animate-slide-in { animation: slideIn 0.3s ease-out; }
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
    </style>
    
    @stack('styles')
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased">
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
            <main class="flex-1 p-4 sm:p-6 overflow-x-hidden">
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
            <footer class="px-4 sm:px-6 py-3 border-t border-slate-200/80 dark:border-slate-800/80 bg-white/50 dark:bg-slate-900/50">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400 dark:text-slate-500">
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
