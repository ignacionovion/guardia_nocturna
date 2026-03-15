{{-- Hero Header - Panel de Control Profesional --}}
<header class="sticky top-0 z-40 bg-gradient-header border-b border-slate-700/50 shadow-xl shadow-black/20">
    <div class="px-6 py-5">
        {{-- Top Row: Title + Live Indicator + Actions --}}
        <div class="flex items-center justify-between gap-6">
            {{-- Left: Title Block --}}
            <div class="flex items-center gap-5">
                {{-- Logo/Icon --}}
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center shadow-lg shadow-red-500/30">
                    <i class="fas fa-shield-halved text-2xl text-white"></i>
                </div>
                
                {{-- Title & Info --}}
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Panel de Control</h1>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-base font-semibold text-slate-300 uppercase tracking-wide">{{ $myGuardia->name }}</span>
                        <span class="text-slate-600">•</span>
                        <span class="text-sm font-medium text-slate-400">{{ $visibleStaffCount }} en pantalla</span>
                        <span class="text-slate-600">•</span>
                        <span class="text-sm font-semibold text-emerald-400">{{ $presentStaffCount }} presentes</span>
                    </div>
                </div>
            </div>
            
            {{-- Center: Live Status Badge --}}
            <div class="hidden lg:flex items-center gap-4">
                @if($isMyGuardiaOnDuty)
                    <div class="flex items-center gap-3 px-5 py-2.5 rounded-xl bg-emerald-500/15 border border-emerald-500/30 badge-glow-success">
                        <div class="w-3 h-3 rounded-full bg-emerald-500 live-indicator"></div>
                        <span class="text-sm font-bold text-emerald-400 uppercase tracking-wider">Guardia en Vivo</span>
                    </div>
                @else
                    <div class="flex items-center gap-3 px-5 py-2.5 rounded-xl bg-slate-700/50 border border-slate-600/50">
                        <div class="w-3 h-3 rounded-full bg-slate-500"></div>
                        <span class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Fuera de Turno</span>
                    </div>
                @endif
            </div>
            
            {{-- Right: Action Buttons --}}
            <div class="flex items-center gap-3">
                {{-- Quick Actions --}}
                <div class="flex items-center gap-2">
                    <button onclick="toggleFullscreen()" class="icon-btn flex items-center justify-center" title="Pantalla completa">
                        <i class="fas fa-expand text-slate-400 text-3xl"></i>
                    </button>
                    <button onclick="openAseoModal()" class="icon-btn flex items-center justify-center" title="Asignación de Aseo">
                        <i class="fas fa-broom text-red-400 text-3xl"></i>
                    </button>
                    <button onclick="openCalendarPopup()" class="icon-btn flex items-center justify-center" title="Calendario">
                        <i class="fas fa-calendar-days text-emerald-400 text-3xl"></i>
                    </button>
                    @if(feature('emergencias'))
                        <button onclick="openEmergenciasModal()" class="icon-btn flex items-center justify-center" title="Emergencias">
                            <i class="fas fa-truck-medical text-amber-400 text-3xl"></i>
                        </button>
                    @endif
                    <button onclick="openRefuerzoModal()" class="icon-btn flex items-center justify-center" title="Agregar Refuerzo">
                        <i class="fas fa-user-plus text-sky-400 text-3xl"></i>
                    </button>
                </div>
                
                {{-- Divider --}}
                <div class="w-px h-8 bg-slate-700"></div>
                
                {{-- Save Button --}}
                <button 
                    id="guardia-attendance-submit" 
                    form="guardia-attendance-form" 
                    type="submit" 
                    {{ $attendanceButtonDisabled ? 'disabled' : '' }}
                    class="flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm transition-all {{ $attendanceButtonDisabled ? 'bg-slate-800/50 text-slate-500 border border-slate-700/50 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-500/25' }}"
                >
                    <i class="fas fa-floppy-disk"></i>
                    <span class="hidden sm:inline">Guardar</span>
                </button>
                
                {{-- Status Badge --}}
                @php
                    $badgeClasses = match($attendanceVariant) {
                        'success' => 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400 badge-glow-success',
                        'warning' => 'bg-amber-500/15 border-amber-500/30 text-amber-400 badge-glow-warning',
                        'danger' => 'bg-red-500/15 border-red-500/30 text-red-400 badge-glow-danger',
                        default => 'bg-slate-700/50 border-slate-600/50 text-slate-400',
                    };
                @endphp
                <div id="attendance-saved-badge" class="hidden sm:flex items-center px-4 py-2 rounded-xl border text-xs font-bold uppercase tracking-wider {{ $badgeClasses }}">
                    {{ $attendanceMessage }}
                </div>
                
                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="icon-btn" title="Cerrar sesión">
                        <i class="fas fa-right-from-bracket text-rose-400"></i>
                    </button>
                </form>
            </div>
        </div>
        
        {{-- Mobile: Live Status (shown below on small screens) --}}
        <div class="lg:hidden mt-4 flex items-center justify-center">
            @if($isMyGuardiaOnDuty)
                <div class="flex items-center gap-3 px-5 py-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 live-indicator"></div>
                    <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Guardia en Vivo</span>
                </div>
            @else
                <div class="flex items-center gap-3 px-5 py-2 rounded-xl bg-slate-700/50 border border-slate-600/50">
                    <div class="w-2.5 h-2.5 rounded-full bg-slate-500"></div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Fuera de Turno</span>
                </div>
            @endif
        </div>
    </div>
</header>

@include('dashboard.partials.guardia._stale_banner')
