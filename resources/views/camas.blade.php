@extends('layouts.modern')

@section('title', 'Gestión de Camas - ' . branding()->nombre_empresa)
@section('page-title', 'Gestión de Camas')

@section('content')
    <x-ui.page-header title="Gestión de Camas" subtitle="Control de ocupación y asignaciones en tiempo real" icon="fas fa-bed" iconVariant="emerald">
        @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania']))
            <x-ui.button 
                variant="info" 
                size="sm" 
                icon="fas fa-cog" 
                href="{{ route('admin.beds.index') }}"
                :disabled="isset($limitData) && !$limitData['can_create']"
                :title="(isset($limitData) && !$limitData['can_create']) ? $limitData['message'] : 'Configurar camas existentes y agregar nuevas'">
                Configurar Camas
            </x-ui.button>
        @endif
    </x-ui.page-header>

    @if(isset($limitData) && !$limitData['can_create'])
        <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                <p class="font-bold">{{ $limitData['message'] }}</p>
            </div>
        </div>
    @endif

    @php
        $sectorSummary = $beds
            ->groupBy(fn ($bed) => $bed->room ?: 'Sin sector asignado')
            ->map(function ($roomBeds, $roomName) {
                $total = $roomBeds->count();
                $occupied = $roomBeds->filter(fn ($bed) => $bed->status === 'occupied')->count();
                $available = $roomBeds->filter(fn ($bed) => $bed->status === 'available')->count();
                $maintenance = $roomBeds->filter(fn ($bed) => $bed->status === 'maintenance')->count();
                $occupiedPct = $total > 0 ? round(($occupied / $total) * 100) : 0;

                return [
                    'name' => $roomName,
                    'total' => $total,
                    'occupied' => $occupied,
                    'available' => $available,
                    'maintenance' => $maintenance,
                    'occupiedPct' => $occupiedPct,
                ];
            })
            ->sortByDesc('occupiedPct')
            ->values();
    @endphp

    {{-- KPIs + filtros (tablero operativo) --}}
    <x-ui.card class="mb-6 overflow-hidden border-slate-200/90 shadow-sm ring-1 ring-slate-900/[0.04] dark:border-slate-700 dark:ring-white/[0.06]">
        <div class="border-b border-slate-100 bg-gradient-to-b from-slate-50/80 to-white px-5 py-4 dark:border-slate-800 dark:from-slate-900/40 dark:to-slate-900/20">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500">Panel en vivo</p>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Estado del parque de camas</h2>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">Métricas al instante · filtra por estado</p>
            </div>
        </div>

        <div class="p-5 pt-5">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
                <div class="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-gradient-to-br from-slate-50 to-white p-4 shadow-[0_1px_0_0_rgba(15,23,42,0.04)] dark:border-slate-700 dark:from-slate-800/50 dark:to-slate-900/30">
                    <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-slate-200/40 dark:bg-slate-600/20"></div>
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total</p>
                            <p class="mt-1 text-3xl font-bold tabular-nums leading-none tracking-tight text-slate-900 dark:text-white">{{ $totalBeds }}</p>
                            <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500">Capacidad registrada</p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-white shadow-inner dark:bg-white dark:text-slate-900">
                            <i class="fas fa-layer-group text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-red-200/80 bg-gradient-to-br from-red-50/90 to-white p-4 shadow-[0_1px_0_0_rgba(220,38,38,0.06)] dark:border-red-900/40 dark:from-red-950/40 dark:to-slate-900/30">
                    <div class="pointer-events-none absolute -right-5 -top-5 h-20 w-20 rounded-full bg-red-200/35 dark:bg-red-900/30"></div>
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-red-600/90 dark:text-red-400">Ocupadas</p>
                            <p class="mt-1 text-3xl font-bold tabular-nums leading-none tracking-tight text-red-700 dark:text-red-300">{{ $occupiedBeds }}</p>
                            <p class="mt-2 text-[11px] text-red-600/70 dark:text-red-400/80">En uso ahora</p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white shadow-sm">
                            <i class="fas fa-user text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-emerald-50/90 to-white p-4 shadow-[0_1px_0_0_rgba(5,150,105,0.06)] dark:border-emerald-900/40 dark:from-emerald-950/35 dark:to-slate-900/30">
                    <div class="pointer-events-none absolute -right-5 -top-5 h-20 w-20 rounded-full bg-emerald-200/35 dark:bg-emerald-900/25"></div>
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700/90 dark:text-emerald-400">Disponibles</p>
                            <p class="mt-1 text-3xl font-bold tabular-nums leading-none tracking-tight text-emerald-700 dark:text-emerald-300">{{ $availableBeds }}</p>
                            <p class="mt-2 text-[11px] text-emerald-700/70 dark:text-emerald-400/80">Listas para asignar</p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                            <i class="fas fa-circle-check text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/90 to-white p-4 shadow-[0_1px_0_0_rgba(217,119,6,0.06)] dark:border-amber-900/40 dark:from-amber-950/35 dark:to-slate-900/30">
                    <div class="pointer-events-none absolute -right-5 -top-5 h-20 w-20 rounded-full bg-amber-200/35 dark:bg-amber-900/25"></div>
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-800/90 dark:text-amber-400">Mantención</p>
                            <p class="mt-1 text-3xl font-bold tabular-nums leading-none tracking-tight text-amber-800 dark:text-amber-200">{{ $maintenanceBeds }}</p>
                            <p class="mt-2 text-[11px] text-amber-800/65 dark:text-amber-300/80">Fuera de servicio</p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-sm">
                            <i class="fas fa-screwdriver-wrench text-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
                <span class="mr-1 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Vista</span>
                <a href="{{ route('camas') }}" class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all {{ !request('status') ? 'bg-slate-900 text-white shadow-sm ring-1 ring-slate-900/10 dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200/80 hover:bg-slate-200/80 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700' }}">
                    <i class="fas fa-border-all text-[10px] opacity-80"></i> Todas
                </a>
                <a href="{{ route('camas', ['status' => 'occupied']) }}" class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all {{ request('status') === 'occupied' ? 'bg-red-600 text-white shadow-sm ring-1 ring-red-700/20' : 'bg-red-50 text-red-700 ring-1 ring-red-200/70 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900/50' }}">
                    <i class="fas fa-user text-[10px] opacity-90"></i> Ocupadas
                </a>
                <a href="{{ route('camas', ['status' => 'available']) }}" class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all {{ request('status') === 'available' ? 'bg-emerald-600 text-white shadow-sm ring-1 ring-emerald-700/20' : 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200/70 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900/50' }}">
                    <i class="fas fa-circle-check text-[10px] opacity-90"></i> Disponibles
                </a>
                <a href="{{ route('camas', ['status' => 'maintenance']) }}" class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all {{ request('status') === 'maintenance' ? 'bg-amber-500 text-white shadow-sm ring-1 ring-amber-600/25' : 'bg-amber-50 text-amber-900 ring-1 ring-amber-200/70 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-200 dark:ring-amber-900/50' }}">
                    <i class="fas fa-screwdriver-wrench text-[10px] opacity-90"></i> Mantención
                </a>
            </div>

            <details class="group mt-5 border-t border-dashed border-slate-200 pt-4 dark:border-slate-700">
                <summary class="cursor-pointer list-none text-xs font-medium text-slate-500 transition-colors hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 [&::-webkit-details-marker]:hidden">
                    <span class="inline-flex items-center gap-2">
                        <i class="fas fa-chevron-right text-[10px] text-slate-400 transition-transform group-open:rotate-90"></i>
                        Resumen opcional por sector
                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $sectorSummary->count() }} sectores</span>
                    </span>
                </summary>
                <div class="mt-4 overflow-x-auto pb-1">
                    @if($sectorSummary->isEmpty())
                        <p class="py-3 text-center text-sm text-slate-500 dark:text-slate-400">Sin datos agrupados por sector.</p>
                    @else
                        <div class="flex min-w-max gap-2">
                            @foreach($sectorSummary as $sector)
                                <div class="w-[200px] shrink-0 rounded-xl border border-slate-200/90 bg-slate-50/80 px-3 py-2.5 dark:border-slate-700 dark:bg-slate-800/40">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="truncate text-xs font-bold text-slate-800 dark:text-slate-100" title="{{ $sector['name'] }}">{{ $sector['name'] }}</p>
                                        <span class="shrink-0 text-[10px] font-semibold tabular-nums text-slate-500">{{ $sector['occupiedPct'] }}%</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-1 text-[10px] font-semibold">
                                        <span class="rounded-md bg-red-100 px-1.5 py-0.5 text-red-800 dark:bg-red-950/50 dark:text-red-300">{{ $sector['occupied'] }} oc.</span>
                                        <span class="rounded-md bg-emerald-100 px-1.5 py-0.5 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">{{ $sector['available'] }} lib.</span>
                                        <span class="rounded-md bg-amber-100 px-1.5 py-0.5 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200">{{ $sector['maintenance'] }} mant.</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </details>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($beds as $bed)
            @php
                $isAvailable = $bed->status == 'available';
                $isOccupied = $bed->status == 'occupied';
                $isMaintenance = $bed->status == 'maintenance';
                $isDisabled = $bed->status == 'disabled';
                $bedTitle = $bed->name ?? ('N° ' . $bed->number);
                $bedLabelForJs = json_encode((string) ($bed->name ?? $bed->number));
                $accentBar = $isOccupied ? 'bg-red-500' : ($isAvailable ? 'bg-emerald-500' : ($isMaintenance ? 'bg-amber-500' : 'bg-slate-400'));
                $heroRing = $isOccupied ? 'ring-red-200/80 dark:ring-red-900/50' : ($isAvailable ? 'ring-emerald-200/80 dark:ring-emerald-900/40' : 'ring-slate-200 dark:ring-slate-700');
                $heroBg = $isOccupied ? 'bg-red-50 dark:bg-red-950/40' : ($isAvailable ? 'bg-emerald-50 dark:bg-emerald-950/35' : 'bg-slate-100 dark:bg-slate-800/60');
                $heroIcon = $isOccupied ? 'fa-user text-red-600 dark:text-red-400' : ($isAvailable ? 'fa-circle-check text-emerald-600 dark:text-emerald-400' : ($isMaintenance ? 'fa-screwdriver-wrench text-amber-600 dark:text-amber-400' : 'fa-ban text-slate-500'));
            @endphp

            <x-ui.card
                padding="none"
                hover
                class="relative flex min-h-[300px] flex-col overflow-hidden border-slate-200/90 shadow-sm ring-1 ring-slate-900/[0.04] dark:border-slate-700 dark:bg-slate-900/20 dark:ring-white/[0.06]"
            >
                <span class="absolute bottom-0 left-0 top-0 w-1 rounded-l-2xl {{ $accentBar }}" aria-hidden="true"></span>

                <div class="flex min-w-0 flex-1 flex-col pl-1">
                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 pb-3 pt-4 dark:border-slate-800">
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">Cama</p>
                            <h3 class="mt-0.5 truncate text-lg font-bold leading-tight tracking-tight text-slate-900 dark:text-white" title="{{ $bedTitle }}">{{ $bedTitle }}</h3>
                            @if($bed->room)
                                <p class="mt-1 truncate text-xs font-medium text-slate-500 dark:text-slate-400">
                                    <i class="fas fa-location-dot mr-1 text-[10px] opacity-70"></i>{{ $bed->room }}
                                </p>
                            @endif
                        </div>
                        @if($isOccupied)
                            <x-ui.badge variant="danger" size="sm" dot class="shrink-0">Ocupada</x-ui.badge>
                        @elseif($isAvailable)
                            <x-ui.badge variant="success" size="sm" dot class="shrink-0">Libre</x-ui.badge>
                        @elseif($isMaintenance)
                            <x-ui.badge variant="warning" size="sm" dot class="shrink-0">Mantención</x-ui.badge>
                        @else
                            <x-ui.badge variant="default" size="sm" class="shrink-0">No disponible</x-ui.badge>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col px-4 py-4">
                        <div class="mx-auto mb-4 flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl ring-2 ring-inset {{ $heroRing }} {{ $heroBg }}">
                            <i class="fas {{ $heroIcon }} text-2xl"></i>
                        </div>

                        @if($isOccupied && $bed->currentAssignment)
                            @php
                                $firefighter = $bed->currentAssignment->firefighter;
                                $initials = strtoupper(substr($firefighter?->nombres ?? '', 0, 1) . substr($firefighter?->apellido_paterno ?? '', 0, 1));
                                $fullName = trim(($firefighter?->nombres ?? '') . ' ' . ($firefighter?->apellido_paterno ?? ''));
                                $startTime = $bed->currentAssignment->started_at ?? $bed->currentAssignment->assigned_at;
                            @endphp
                            <div class="rounded-xl border border-red-200/90 bg-red-50/80 p-3 dark:border-red-900/50 dark:bg-red-950/30">
                                <div class="flex items-center gap-3">
                                    @if($firefighter?->photo_path)
                                        <img src="{{ route('media', $firefighter->photo_path) }}" class="h-11 w-11 shrink-0 rounded-xl object-cover ring-2 ring-white dark:ring-slate-800" alt="{{ $fullName }}">
                                    @else
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-200 text-sm font-bold text-red-800 dark:bg-red-900/60 dark:text-red-200">
                                            {{ $initials ?: '?' }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-red-600/90 dark:text-red-400">Ocupante</p>
                                        <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $fullName ?: '—' }}</p>
                                    </div>
                                </div>
                                @if($startTime)
                                    <div class="mt-3 flex items-center justify-between gap-2 rounded-lg bg-white/90 px-2.5 py-2 text-xs dark:bg-slate-900/50">
                                        <span class="text-slate-600 dark:text-slate-300"><i class="fas fa-clock mr-1 text-red-500"></i>{{ $startTime->format('H:i') }}</span>
                                        <span class="shrink-0 font-semibold text-red-700 dark:text-red-300">{{ $startTime->diffForHumans() }}</span>
                                    </div>
                                @endif
                            </div>
                            @if($bed->currentAssignment->notes)
                                <div class="mt-2 rounded-lg border border-amber-200/80 bg-amber-50/90 px-2.5 py-2 dark:border-amber-900/40 dark:bg-amber-950/25">
                                    <p class="line-clamp-2 text-xs text-amber-900 dark:text-amber-100"><i class="fas fa-sticky-note mr-1 opacity-70"></i>{{ $bed->currentAssignment->notes }}</p>
                                </div>
                            @endif
                        @elseif($isOccupied)
                            <div class="rounded-lg border border-amber-200/80 bg-amber-50/90 px-3 py-2.5 text-center dark:border-amber-900/40 dark:bg-amber-950/20">
                                <p class="text-xs font-medium text-amber-900 dark:text-amber-200">Marcada como ocupada sin asignación cargada.</p>
                            </div>
                        @elseif($isAvailable)
                            <div class="text-center">
                                <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Lista para asignar</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Sin ocupante registrado</p>
                            </div>
                        @elseif($isMaintenance || $isDisabled)
                            <div class="text-center">
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $isMaintenance ? 'Fuera de servicio temporal' : 'Cama deshabilitada' }}</p>
                                @if($bed->notes ?? $bed->description)
                                    <p class="mt-2 line-clamp-3 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $bed->notes ?? $bed->description }}</p>
                                @endif
                            </div>
                        @else
                            <div class="text-center text-sm text-slate-500 dark:text-slate-400">Estado sin detalle adicional.</div>
                        @endif
                    </div>

                    <div class="mt-auto space-y-2 border-t border-slate-100 bg-slate-50/70 px-4 py-3 dark:border-slate-800 dark:bg-slate-950/40">
                        @if($isAvailable)
                            <x-ui.button type="button" variant="success" size="sm" icon="fas fa-user-plus" class="w-full" onclick="openAssignModal('{{ $bed->id }}', {{ $bedLabelForJs }})">
                                Asignar voluntario
                            </x-ui.button>
                            @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania']))
                                <div class="grid grid-cols-2 gap-2">
                                    <x-ui.button type="button" variant="outline" size="xs" icon="fas fa-qrcode" class="w-full !font-semibold" onclick="openQrModal('{{ $bed->id }}', {{ $bedLabelForJs }})">
                                        QR
                                    </x-ui.button>
                                    <form action="{{ route('beds.maintenance', $bed->id) }}" method="POST" onsubmit="return confirm('¿Marcar en mantención?');" class="contents">
                                        @csrf
                                        @method('PUT')
                                        <x-ui.button type="submit" variant="outline" size="xs" icon="fas fa-screwdriver-wrench" class="w-full !font-semibold">
                                            Mantención
                                        </x-ui.button>
                                    </form>
                                </div>
                            @endif
                        @elseif($isOccupied)
                            @if($bed->currentAssignment)
                                <form action="{{ route('beds.release', $bed->currentAssignment->id) }}" method="POST" onsubmit="return confirm('¿Liberar esta cama?');" class="block w-full">
                                    @csrf
                                    <input type="hidden" name="release" value="1">
                                    <x-ui.button type="submit" variant="success" size="sm" icon="fas fa-door-open" class="w-full">
                                        Liberar cama
                                    </x-ui.button>
                                </form>
                            @endif
                        @else
                            @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania']) && ($isMaintenance || $isDisabled))
                                <form action="{{ route('beds.available', $bed->id) }}" method="POST" onsubmit="return confirm('¿Habilitar como disponible?');" class="block w-full">
                                    @csrf
                                    @method('PUT')
                                    <x-ui.button type="submit" variant="success" size="sm" icon="fas fa-circle-check" class="w-full">
                                        Habilitar cama
                                    </x-ui.button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </x-ui.card>
        @endforeach
    </div>

    <script>
        function getModalDialog(modalEl) {
            return modalEl ? modalEl.querySelector('[data-modal-dialog]') : null;
        }

        function openAssignModal(bedId, bedNumber) {
            document.getElementById('modalBedId').value = bedId;
            document.getElementById('modalBedNumber').innerText = '#' + bedNumber;
            
            const modal = document.getElementById('assignModal');
            const dialog = getModalDialog(modal);
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                if (dialog) {
                    dialog.classList.remove('scale-95');
                    dialog.classList.add('scale-100');
                }
            }, 10);
        }

        function closeAssignModal() {
            const modal = document.getElementById('assignModal');
            const dialog = getModalDialog(modal);
            modal.classList.add('opacity-0');
            if (dialog) {
                dialog.classList.remove('scale-100');
                dialog.classList.add('scale-95');
            }
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function openQrModal(bedId, bedNumber) {
            const qrUrl = `{{ url('/camas/scan') }}/${bedId}`;
            const printUrl = `/camas/${bedId}/qr/imprimir`;
            document.getElementById('qrBedNumber').innerText = '#' + bedNumber;
            document.getElementById('qrCodeContainer').innerHTML = '';
            
            new QRCode(document.getElementById('qrCodeContainer'), {
                text: qrUrl,
                width: 200,
                height: 200,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
            
            document.getElementById('qrUrl').innerText = qrUrl;
            document.getElementById('qrUrl').href = qrUrl;
            document.getElementById('qrPrintLink').href = printUrl;
            
            const modal = document.getElementById('qrModal');
            const dialog = getModalDialog(modal);
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                if (dialog) {
                    dialog.classList.remove('scale-95');
                    dialog.classList.add('scale-100');
                }
            }, 10);
        }

        function closeQrModal() {
            const modal = document.getElementById('qrModal');
            const dialog = getModalDialog(modal);
            modal.classList.add('opacity-0');
            if (dialog) {
                dialog.classList.remove('scale-100');
                dialog.classList.add('scale-95');
            }
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const assignModal = document.getElementById('assignModal');
            const qrModal = document.getElementById('qrModal');
            if (event.target == assignModal) {
                closeAssignModal();
            }
            if (event.target == qrModal) {
                closeQrModal();
            }
        }
        
        // Cerrar con Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeAssignModal();
                closeQrModal();
            }
        });
    </script>
    
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endsection

@push('modals')
    <div id="assignModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="assignModalHeading">
            <div data-modal-dialog class="relative w-full max-w-md overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl transform scale-95 transition-all duration-300">
                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
                    <h3 id="assignModalHeading" class="text-white font-bold text-sm uppercase tracking-wide flex items-center">
                        <i class="fas fa-bed mr-2 text-emerald-400"></i> Asignar Cama <span id="modalBedNumber" class="ml-1 text-white"></span>
                    </h3>
                    <button type="button" onclick="closeAssignModal()" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="card-body">
                    <form id="assignForm" method="POST" action="{{ route('beds.assign') }}">
                        @csrf
                        <input type="hidden" name="bed_id" id="modalBedId">
                        <div class="mb-5">
                            <label class="form-label">Voluntario</label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <select name="firefighter_id" class="form-select pl-9" required>
                                    <option value="">Seleccione voluntario...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->nombres }} {{ $user->apellido_paterno }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-6">
                            <label class="form-label">Comentario / Nota <span class="text-slate-400 font-normal normal-case">(Opcional)</span></label>
                            <div class="relative">
                                <i class="fas fa-comment-dots absolute left-3 top-3 text-slate-400 text-xs"></i>
                                <textarea name="notes" class="form-input pl-9 min-h-[80px]" placeholder="Ej: Se retira a las 07:00 hrs..."></textarea>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <x-ui.button type="button" variant="ghost" size="sm" class="w-1/2" onclick="closeAssignModal()">
                                Cancelar
                            </x-ui.button>
                            <x-ui.button type="submit" variant="primary" size="sm" icon="fas fa-check" class="w-1/2">
                                Confirmar
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
    </div>

    <div id="qrModal" class="fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm hidden opacity-0 transition-all duration-300 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="qrModalHeading">
            <div data-modal-dialog class="relative w-full max-w-md overflow-hidden bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl transform scale-95 transition-all duration-300">
                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
                    <h3 id="qrModalHeading" class="text-white font-bold text-sm uppercase tracking-wide flex items-center">
                        <i class="fas fa-qrcode mr-2 text-cyan-400"></i> QR Cama <span id="qrBedNumber" class="ml-1 text-white"></span>
                    </h3>
                    <button type="button" onclick="closeQrModal()" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="card-body text-center">
                    <p class="text-body-sm mb-4">Escanea este código con tu teléfono para asignarte esta cama</p>
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 mb-4 inline-block">
                        <div id="qrCodeContainer" class="flex justify-center"></div>
                    </div>
                    <div class="card !bg-white dark:!bg-slate-800 mb-4">
                        <p class="text-caption mb-1">URL</p>
                        <a id="qrUrl" href="#" target="_blank" class="text-sm text-cyan-600 font-mono break-all hover:text-cyan-500 transition-colors"></a>
                    </div>
                    <x-ui.button variant="info" size="md" icon="fas fa-print" class="w-full mb-3" id="qrPrintLink" href="#" target="_blank">
                        Imprimir
                    </x-ui.button>
                    <x-ui.button variant="primary" size="sm" class="w-full" onclick="closeQrModal()">
                        Cerrar
                    </x-ui.button>
                </div>
            </div>
    </div>
@endpush
