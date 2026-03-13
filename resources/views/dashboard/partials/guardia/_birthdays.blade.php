{{-- Widget de cumpleaños --}}
<x-ui.card class="!bg-slate-900 !border-slate-800">
    <x-slot:header>
        <div class="flex items-center justify-between w-full">
            <div class="text-label">Próximos Cumpleaños</div>
            <div class="text-caption">{{ $currentMonthName }}</div>
        </div>
    </x-slot:header>
    @if($birthdaysList->isEmpty())
        <div class="text-body text-slate-400">Sin cumpleaños este mes.</div>
    @else
        <div class="space-y-3">
            @foreach($birthdaysList->take(5) as $user)
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-body font-semibold text-white truncate">{{ $user->nombres }} {{ $user->apellido_paterno }}</div>
                        <div class="text-caption">Bombero</div>
                    </div>
                    <div class="text-body font-semibold text-slate-400">
                        {{ \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d') }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-ui.card>
