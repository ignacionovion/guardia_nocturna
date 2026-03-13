{{-- Header del panel de guardia --}}
<div class="sticky top-0 z-40 flex flex-col md:flex-row md:items-center md:justify-between mb-5 gap-4 border-b border-slate-800 pb-4 bg-slate-950">
    <div class="flex items-center gap-3 min-w-0">
        <div class="icon-box icon-box-gradient-red icon-box-md">
            <i class="fas fa-gauge-high"></i>
        </div>
        <div class="min-w-0">
            <div class="text-label">Panel de Control</div>
            <div class="mt-0.5 flex items-center gap-3 min-w-0">
                <div class="text-title-md text-white uppercase truncate">{{ $myGuardia->name }}</div>
                @if($isMyGuardiaOnDuty)
                    <x-ui.badge variant="success" size="sm">SEMANA DE GUARDIA</x-ui.badge>
                @else
                    <x-ui.badge variant="default" size="sm">FUERA DE TURNO</x-ui.badge>
                @endif
            </div>
            <div class="mt-0.5 text-body-sm text-slate-400">{{ $visibleStaffCount }} en pantalla | {{ $presentStaffCount }} presentes</div>
        </div>
    </div>

    @include('dashboard.partials.guardia._stale_banner')

    <div class="w-full md:flex-1 flex items-center justify-start md:justify-center">
        <div class="flex items-center gap-2">
            <x-ui.button variant="secondary" size="sm" onclick="toggleFullscreen()" class="!w-10 !h-10 !p-0" title="Pantalla completa">
                <i class="fas fa-expand text-sm"></i>
            </x-ui.button>
            <x-ui.button variant="secondary" size="sm" href="{{ route('guardia.aseo') }}" class="!w-10 !h-10 !p-0" title="Asignación de Aseo">
                <i class="fas fa-broom text-sm text-red-400"></i>
            </x-ui.button>
            <x-ui.button variant="secondary" size="sm" onclick="openCalendarPopup()" class="!w-10 !h-10 !p-0" title="Calendario de Guardias">
                <i class="fas fa-calendar-days text-sm text-emerald-400"></i>
            </x-ui.button>
            @if(feature('emergencias'))
                <x-ui.button variant="secondary" size="sm" href="{{ route('admin.emergencies.index') }}" class="!w-10 !h-10 !p-0" title="Emergencias">
                    <i class="fas fa-truck-medical text-sm text-amber-400"></i>
                </x-ui.button>
            @endif
            <x-ui.button variant="secondary" size="sm" onclick="openRefuerzoModal()" class="!w-10 !h-10 !p-0" title="Refuerzo">
                <i class="fas fa-user-plus text-sm text-sky-400"></i>
            </x-ui.button>
            <button 
                id="guardia-attendance-submit" 
                form="guardia-attendance-form" 
                type="submit" 
                {{ $attendanceButtonDisabled ? 'disabled' : '' }}
                class="w-10 h-10 rounded-xl flex items-center justify-center transition-all border {{ $attendanceButtonClass }}"
            >
                <i class="fas fa-floppy-disk text-sm {{ $attendanceIconClass }}"></i>
            </button>
        </div>
    </div>

    <div class="flex items-center justify-between md:justify-end gap-3 shrink-0">
        <x-ui.badge id="attendance-saved-badge" :variant="$attendanceVariant" size="sm">{{ $attendanceMessage }}</x-ui.badge>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button variant="secondary" size="sm" type="submit" class="!h-10" title="Cerrar sesión">
                <i class="fas fa-right-from-bracket text-sm text-rose-400"></i>
                <span class="hidden sm:inline">Salir</span>
            </x-ui.button>
        </form>
    </div>
</div>
