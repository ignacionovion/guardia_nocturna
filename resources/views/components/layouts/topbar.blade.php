@props([
    'title' => '',
])

@php
$user = Auth::user();
$currentDate = now()->locale('es');
@endphp

<header class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 sm:px-6 bg-white/80 dark:bg-slate-900/80 glass border-b border-slate-200/80 dark:border-slate-800/80">
    {{-- Left: Mobile menu + Title + Date --}}
    <div class="flex items-center gap-4">
        {{-- Mobile sidebar toggle --}}
        <button @click="mobileSidebarOpen = !mobileSidebarOpen" 
                class="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="fas fa-bars"></i>
        </button>
        
        {{-- Page title & date --}}
        <div class="hidden sm:flex flex-col">
            @if($title)
            <h1 class="text-base font-semibold text-slate-900 dark:text-white leading-tight">{{ $title }}</h1>
            @endif
            <span class="text-xs text-slate-500 dark:text-slate-400">
                {{ $currentDate->isoFormat('dddd, D [de] MMMM') }}
            </span>
        </div>
    </div>
    
    {{-- Center: Quick Actions (Desktop) --}}
    <div class="hidden lg:flex items-center gap-2">
        <a href="{{ route('guardia.now') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition-colors">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Guardia en Vivo
        </a>
    </div>
    
    {{-- Right: Actions --}}
    <div class="flex items-center gap-1 sm:gap-2">
        {{-- Search --}}
        <div class="hidden md:block relative" x-data="{ focused: false }">
            <input type="text" 
                   placeholder="Buscar..." 
                   @focus="focused = true"
                   @blur="focused = false"
                   class="w-48 lg:w-56 pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-white focus:border-transparent transition-all"
                   :class="focused ? 'w-72' : ''">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <kbd class="absolute right-2.5 top-1/2 -translate-y-1/2 hidden lg:inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-medium text-slate-400 bg-slate-100 dark:bg-slate-700 rounded">
                ⌘K
            </kbd>
        </div>
        
        {{-- Theme toggle --}}
        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                class="p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                title="Cambiar tema">
            <i class="fas text-sm" :class="darkMode ? 'fa-sun text-amber-500' : 'fa-moon'"></i>
        </button>
        
        {{-- Notifications --}}
        @if(in_array($user->role ?? '', ['super_admin', 'capitania']))
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="relative p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i class="fas fa-bell text-sm"></i>
                <span id="notification-badge-topbar" 
                      class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full hidden"></span>
            </button>
            
            {{-- Dropdown --}}
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden"
                 style="display: none;">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                    <span class="font-semibold text-slate-900 dark:text-white text-sm">Notificaciones</span>
                    <button class="text-[11px] text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-medium">Marcar leídas</button>
                </div>
                <div id="notification-list-topbar" class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700">
                    <div class="p-6 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center">
                            <i class="fas fa-bell-slash text-slate-400"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Sin notificaciones</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Estás al día</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        {{-- Separator --}}
        <div class="hidden sm:block w-px h-6 bg-slate-200 dark:bg-slate-700 mx-1"></div>
        
        {{-- User menu --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="flex items-center gap-2.5 p-1.5 pr-3 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 flex items-center justify-center text-white dark:text-slate-900 text-xs font-bold">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name ?? 'U')[1] ?? '', 0, 1)) }}
                </div>
                <div class="hidden sm:block text-left">
                    <div class="text-sm font-medium text-slate-900 dark:text-white leading-tight">{{ $user->name ?? 'Usuario' }}</div>
                    <div class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ str_replace('_', ' ', $user->role ?? '') }}</div>
                </div>
                <i class="fas fa-chevron-down text-[10px] text-slate-400 hidden sm:block"></i>
            </button>
            
            {{-- Dropdown --}}
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden"
                 style="display: none;">
                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <div class="font-medium text-slate-900 dark:text-white text-sm">{{ $user->name ?? 'Usuario' }}</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $user->email ?? '' }}</div>
                </div>
                <div class="py-1">
                    @if(($user->role ?? '') === 'super_admin')
                    <a href="{{ route('admin.system.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <i class="fas fa-sliders w-4 text-center text-slate-400"></i>
                        Configuración
                    </a>
                    @if(addon('custom_branding'))
                    <a href="{{ route('admin.branding.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <i class="fas fa-palette w-4 text-center text-slate-400"></i>
                        Personalización
                    </a>
                    @endif
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <i class="fas fa-user-shield w-4 text-center text-slate-400"></i>
                        Usuarios
                    </a>
                    @endif
                </div>
                <div class="border-t border-slate-100 dark:border-slate-700 p-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                            <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
