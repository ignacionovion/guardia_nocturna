@props([
    'title' => '',
])

@php
$user = Auth::user();
$tenant = tenant();
$currentDate = now()->locale('es');
@endphp

<header class="sticky top-0 z-30 flex items-center justify-between h-16 px-4 sm:px-6 bg-white/95 backdrop-blur-sm border-b border-[#dbe4ee] shadow-sm">
    {{-- Left: Mobile menu + Title + Date --}}
    <div class="flex items-center gap-4">
        {{-- Mobile sidebar toggle --}}
        <button @click="mobileSidebarOpen = !mobileSidebarOpen" 
                class="lg:hidden p-2 rounded-xl text-[#475569] hover:bg-[#eef3f9] transition-colors">
            <i class="fas fa-bars"></i>
        </button>
        
        {{-- Page title & date --}}
        <div class="hidden sm:flex flex-col">
            @if($title)
            <h1 class="text-base font-semibold text-[#0f172a] leading-tight">{{ $title }}</h1>
            @endif
            <span class="text-xs text-[#475569]">
                {{ $currentDate->isoFormat('dddd, D [de] MMMM') }}
            </span>
        </div>
    </div>
    
    {{-- Center: Quick Actions (Desktop) --}}
    <div class="hidden lg:flex items-center gap-2">
        <a href="{{ route('guardia.now') }}" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors">
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
                   class="w-48 lg:w-56 pl-9 pr-3 py-2 text-sm rounded-xl border border-[#dbe4ee] bg-white text-[#0f172a] placeholder-[#475569] focus:outline-none focus:ring-2 focus:ring-[#2563eb]/20 focus:border-[#2563eb] transition-all"
                   :class="focused ? 'w-72' : ''">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#475569] text-xs"></i>
            <kbd class="absolute right-2.5 top-1/2 -translate-y-1/2 hidden lg:inline-flex items-center gap-0.5 px-1.5 py-0.5 text-[10px] font-medium text-[#475569] bg-[#eef3f9] border border-[#dbe4ee] rounded">
                ⌘K
            </kbd>
        </div>
        
        {{-- Separator --}}
        <div class="hidden sm:block w-px h-6 bg-[#dbe4ee] mx-1"></div>
        
        {{-- Notifications --}}
        @if(in_array($user->role ?? '', ['capitan', 'super_admin', 'capitania']))
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="relative p-2.5 rounded-xl text-[#475569] hover:bg-[#eef3f9] transition-colors">
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
                 class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-[#dbe4ee] overflow-hidden"
                 style="display: none;">
                <div class="px-4 py-3 border-b border-[#dbe4ee] flex items-center justify-between bg-[#f6f8fc]">
                    <span class="font-semibold text-[#0f172a] text-sm">Notificaciones</span>
                    <button class="text-[11px] text-[#475569] hover:text-[#0f172a] font-medium">Marcar leídas</button>
                </div>
                <div id="notification-list-topbar" class="max-h-80 overflow-y-auto divide-y divide-[#dbe4ee]">
                    <div class="p-6 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-[#eef3f9] flex items-center justify-center">
                            <i class="fas fa-bell-slash text-[#475569]"></i>
                        </div>
                        <p class="text-sm font-medium text-[#0f172a]">Sin notificaciones</p>
                        <p class="text-xs text-[#475569] mt-1">Estás al día</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        {{-- Separator --}}
        <div class="hidden sm:block w-px h-6 bg-[#dbe4ee] mx-1"></div>
        
        {{-- User menu --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" 
                    class="flex items-center gap-2.5 p-1.5 pr-3 rounded-xl hover:bg-[#eef3f9] transition-colors">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name ?? 'U')[1] ?? '', 0, 1)) }}
                </div>
                <div class="hidden sm:block text-left">
                    <div class="text-sm font-medium text-[#0f172a] leading-tight">{{ $user->name ?? 'Usuario' }}</div>
                    <div class="text-[10px] text-[#475569] uppercase tracking-wide">{{ str_replace('_', ' ', $user->role ?? '') }}</div>
                </div>
                <i class="fas fa-chevron-down text-[10px] text-[#475569] hidden sm:block"></i>
            </button>
            
            {{-- Dropdown --}}
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-[#dbe4ee] overflow-hidden"
                 style="display: none;">
                <div class="px-4 py-3 border-b border-[#dbe4ee] bg-[#f6f8fc]">
                    <div class="font-medium text-[#0f172a] text-sm">{{ $user->name ?? 'Usuario' }}</div>
                    <div class="text-xs text-[#475569] truncate">{{ $user->email ?? '' }}</div>
                    <div class="text-xs text-[#64748b] mt-1">Plan: {{ $tenant?->planRelation?->nombre ?? 'Sin plan asignado' }}</div>
                </div>
                <div class="py-1">
                    @if(in_array($user->role ?? '', ['capitan', 'super_admin', 'capitania']))
                    <a href="{{ route('admin.system.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#0f172a] hover:bg-[#eef3f9] transition-colors">
                        <i class="fas fa-sliders w-4 text-center text-[#475569]"></i>
                        Configuración
                    </a>
                    @if(addon('custom_branding'))
                    <a href="{{ route('admin.branding.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#0f172a] hover:bg-[#eef3f9] transition-colors">
                        <i class="fas fa-palette w-4 text-center text-[#475569]"></i>
                        Personalización
                    </a>
                    @endif
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#0f172a] hover:bg-[#eef3f9] transition-colors">
                        <i class="fas fa-user-shield w-4 text-center text-[#475569]"></i>
                        Usuarios
                    </a>
                    @endif
                </div>
                <div class="border-t border-[#dbe4ee] p-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-arrow-right-from-bracket w-4 text-center"></i>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
