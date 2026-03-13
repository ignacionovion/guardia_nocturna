{{-- Sidebar derecho con widgets --}}
<div class="space-y-4">
    {{-- Reloj --}}
    <x-ui.card class="!bg-slate-900 !border-slate-800">
        <x-slot:header>
            <div class="flex items-center justify-between w-full">
                <div class="text-label">Hora Local</div>
                <x-ui.badge variant="success" size="xs">EN LÍNEA</x-ui.badge>
            </div>
        </x-slot:header>
        <div class="text-center">
            @unless($attendanceEnabled)
                <x-ui.badge variant="warning" size="xs" class="mb-3">HABILITADO {{ $attendanceEnableTime }} - {{ $attendanceDisableTime }}</x-ui.badge>
            @endunless
            <div class="bg-slate-950 border-2 border-slate-800 rounded-xl py-4 px-6 flex items-center justify-center">
                <span id="digital-clock" class="text-2xl md:text-3xl font-mono font-bold tracking-widest text-white">--:--:--</span>
            </div>
        </div>
    </x-ui.card>

    {{-- Cumpleaños --}}
    @include('dashboard.partials.guardia._birthdays')

    {{-- Novedades --}}
    @include('dashboard.partials.guardia._novelties')

    {{-- Academias --}}
    @include('dashboard.partials.guardia._academies')

    {{-- Camas --}}
    @include('dashboard.partials.guardia._beds')
</div>
