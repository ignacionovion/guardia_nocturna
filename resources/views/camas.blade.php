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
                href="{{ route('admin.beds.index') }}">
                Configurar Camas
            </x-ui.button>
        @endif
    </x-ui.page-header>

    {{-- Dashboard Métricas + Filtros --}}
    <div class="card-base p-5 mb-6">
        {{-- Métricas Compactas --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                <div class="w-12 h-12 bg-slate-200 dark:bg-slate-700 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-bed text-slate-700 dark:text-slate-300 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $totalBeds }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                <div class="w-12 h-12 bg-red-200 dark:bg-red-800 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user text-red-700 dark:text-red-300 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide">Ocupadas</p>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $occupiedBeds }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                <div class="w-12 h-12 bg-emerald-200 dark:bg-emerald-800 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-emerald-700 dark:text-emerald-300 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">Disponibles</p>
                    <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $availableBeds }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl">
                <div class="w-12 h-12 bg-amber-200 dark:bg-amber-800 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-tools text-amber-700 dark:text-amber-300 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide">Mantención</p>
                    <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $maintenanceBeds }}</p>
                </div>
            </div>
        </div>

        {{-- Filtros Rápidos Integrados --}}
        <div class="flex flex-wrap items-center gap-2 pt-4 border-t border-slate-200 dark:border-slate-700">
            <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mr-1">Filtrar:</span>
            <a href="{{ route('camas') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ !request('status') ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <i class="fas fa-list mr-1.5"></i>Todas
            </a>
            <a href="{{ route('camas', ['status' => 'occupied']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('status') === 'occupied' ? 'bg-red-600 text-white shadow-sm' : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/30' }}">
                <i class="fas fa-user mr-1.5"></i>Ocupadas
            </a>
            <a href="{{ route('camas', ['status' => 'available']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('status') === 'available' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-900/30' }}">
                <i class="fas fa-check-circle mr-1.5"></i>Disponibles
            </a>
            <a href="{{ route('camas', ['status' => 'maintenance']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('status') === 'maintenance' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/30' }}">
                <i class="fas fa-tools mr-1.5"></i>Mantención
            </a>
        </div>
    </div>

    <!-- Beds Grid - Modern Professional Design -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($beds as $bed)
            @php
                $isAvailable = $bed->status == 'available';
                $isOccupied = $bed->status == 'occupied';
                $isMaintenance = $bed->status == 'maintenance';
                
                $cardGradient = $isAvailable 
                    ? 'from-emerald-50 via-white to-emerald-50/30' 
                    : ($isOccupied 
                        ? 'from-red-50 via-white to-red-50/30' 
                        : 'from-slate-100 via-slate-50 to-slate-100');
                
                $statusColor = $isAvailable 
                    ? 'emerald' 
                    : ($isOccupied 
                        ? 'red' 
                        : 'slate');
                
                $borderColor = $isAvailable 
                    ? 'border-emerald-200' 
                    : ($isOccupied 
                        ? 'border-red-200' 
                        : 'border-slate-200 dark:border-slate-700');
            @endphp
            
            <div class="card-base overflow-hidden transition-all duration-200 hover:shadow-lg group {{ $isOccupied ? 'border-l-4 border-red-500' : ($isAvailable ? 'border-l-4 border-emerald-500' : 'border-l-4 border-slate-400') }}">
                <div class="p-4 flex flex-col h-full">
                    <!-- Header Compacto -->
                    <div class="flex items-center justify-between mb-3 flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl {{ $isOccupied ? 'bg-red-100 dark:bg-red-900/30' : ($isAvailable ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-slate-100 dark:bg-slate-800') }} flex items-center justify-center flex-shrink-0">
                                <span class="text-xl font-bold {{ $isOccupied ? 'text-red-700 dark:text-red-300' : ($isAvailable ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-600 dark:text-slate-400') }}">{{ $bed->name ?? $bed->number }}</span>
                            </div>
                            <div>
                                <p class="text-xs font-bold {{ $isOccupied ? 'text-red-600 dark:text-red-400' : ($isAvailable ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-600 dark:text-slate-400') }} uppercase tracking-wide">Cama</p>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $bed->name ?? 'N° ' . $bed->number }}</p>
                            </div>
                        </div>
                        <div class="px-2.5 py-1 rounded-lg text-xs font-bold {{ $isOccupied ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : ($isAvailable ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400') }}">
                            {{ $isOccupied ? 'OCUPADA' : ($isAvailable ? 'LIBRE' : 'MANTENCIÓN') }}
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-grow flex flex-col">
                        @if($isOccupied && $bed->currentAssignment)
                            @php
                                $firefighter = $bed->currentAssignment->firefighter;
                                $initials = strtoupper(substr($firefighter?->nombres ?? '', 0, 1) . substr($firefighter?->apellido_paterno ?? '', 0, 1));
                                $fullName = trim(($firefighter?->nombres ?? '') . ' ' . ($firefighter?->apellido_paterno ?? ''));
                            @endphp
                            
                            <!-- Ocupante Limpio -->
                            <div class="bg-red-50 dark:bg-red-900/20 rounded-xl p-3 border border-red-200 dark:border-red-800">
                                <div class="flex items-center gap-2.5 mb-2">
                                    @if($firefighter?->photo_path)
                                        <img src="{{ route('media', $firefighter->photo_path) }}" class="w-10 h-10 rounded-lg object-cover" alt="{{ $fullName }}">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-red-200 dark:bg-red-800 flex items-center justify-center">
                                            <span class="text-sm font-bold text-red-700 dark:text-red-300">{{ $initials ?: '?' }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide">Ocupante</p>
                                        <p class="text-sm font-bold text-red-900 dark:text-white truncate">{{ $fullName }}</p>
                                    </div>
                                </div>
                                @php
                                    $startTime = $bed->currentAssignment->started_at ?? $bed->currentAssignment->assigned_at;
                                @endphp
                                @if($startTime)
                                    <div class="flex items-center justify-between text-xs bg-white dark:bg-red-950 rounded-lg px-2.5 py-1.5">
                                        <span class="text-slate-600 dark:text-slate-300"><i class="fas fa-clock mr-1 text-red-600 dark:text-red-400"></i>{{ $startTime->format('H:i') }} hrs</span>
                                        <span class="font-bold text-red-700 dark:text-red-300">{{ $startTime->diffForHumans() }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Notes -->
                            @if($bed->currentAssignment->notes)
                                <div class="mt-2 p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                                    <p class="text-xs text-amber-800 dark:text-amber-200 line-clamp-2"><i class="fas fa-sticky-note mr-1"></i>{{ $bed->currentAssignment->notes }}</p>
                                </div>
                            @endif
                            
                        @elseif($isAvailable)
                            <!-- Estado Disponible -->
                            <div class="flex flex-col items-center justify-center py-4 text-center">
                                <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center mb-2">
                                    <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 text-xl"></i>
                                </div>
                                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-400">Disponible</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Lista para asignar</p>
                            </div>
                        @else
                            <!-- Estado Mantención -->
                            <div class="flex flex-col items-center justify-center py-4 text-center">
                                <div class="w-14 h-14 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center mb-2">
                                    <i class="fas fa-tools text-slate-500 dark:text-slate-400 text-xl"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-600 dark:text-slate-400">En Mantención</p>
                                @if($bed->notes ?? $bed->description)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">{{ $bed->notes ?? $bed->description }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Acciones -->
                    <div class="mt-auto pt-3 space-y-2 flex-shrink-0">
                        @if($isAvailable)
                            <button onclick="openAssignModal('{{ $bed->id }}', '{{ $bed->name ?? $bed->number }}')" class="w-full px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-user-plus"></i>
                                <span>Asignar</span>
                            </button>
                            @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania']))
                                <div class="flex gap-2">
                                    <button onclick="openQrModal('{{ $bed->id }}', '{{ $bed->name ?? $bed->number }}')" class="flex-1 px-2 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg text-xs transition-all">
                                        <i class="fas fa-qrcode"></i> QR
                                    </button>
                                    <form action="{{ route('beds.maintenance', $bed->id) }}" method="POST" onsubmit="return confirm('¿Marcar en mantención?');" class="flex-1">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="w-full px-2 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-lg text-xs transition-all">
                                            <i class="fas fa-tools"></i> Mantención
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @elseif($isOccupied)
                            @if($bed->currentAssignment)
                                <form action="{{ route('beds.release', $bed->currentAssignment->id) }}" method="POST" onsubmit="return confirm('¿Liberar esta cama?');">
                                    @csrf
                                    <input type="hidden" name="release" value="1">
                                    <button type="submit" class="w-full px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm transition-all flex items-center justify-center gap-2">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Liberar</span>
                                    </button>
                                </form>
                            @endif
                        @else
                            @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania']))
                                <form action="{{ route('beds.available', $bed->id) }}" method="POST" onsubmit="return confirm('¿Habilitar como disponible?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="w-full px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm transition-all flex items-center justify-center gap-2">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Habilitar</span>
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Asignación -->
    <div id="assignModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 transition-all duration-300 opacity-0">
        <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative card w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-all duration-300">
            <!-- Modal Header -->
            <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-sm uppercase tracking-wide flex items-center">
                    <i class="fas fa-bed mr-2 text-emerald-400"></i> Asignar Cama <span id="modalBedNumber" class="ml-1 text-white"></span>
                </h3>
                <button onclick="closeAssignModal()" class="text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
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
    </div>

    <!-- Modal QR -->
    <div id="qrModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 transition-all duration-300 opacity-0">
        <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative card w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-all duration-300">
            <!-- Modal Header -->
            <div class="bg-slate-900 px-6 py-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-sm uppercase tracking-wide flex items-center">
                    <i class="fas fa-qrcode mr-2 text-cyan-400"></i> QR Cama <span id="qrBedNumber" class="ml-1 text-white"></span>
                </h3>
                <button onclick="closeQrModal()" class="text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="card-body text-center">
                <p class="text-body-sm mb-4">Escanea este código con tu teléfono para asignarte esta cama</p>
                
                <!-- QR Code Container -->
                <div class="bg-slate-100 dark:bg-slate-800 rounded-xl p-6 mb-4 inline-block">
                    <div id="qrCodeContainer" class="flex justify-center"></div>
                </div>
                
                <!-- URL -->
                <div class="card !bg-slate-50 dark:!bg-slate-800 mb-4">
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
    </div>

    <script>
        function openAssignModal(bedId, bedNumber) {
            document.getElementById('modalBedId').value = bedId;
            document.getElementById('modalBedNumber').innerText = '#' + bedNumber;
            
            const modal = document.getElementById('assignModal');
            modal.classList.remove('hidden');
            // Animation
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('div').classList.remove('scale-95');
                modal.querySelector('div').classList.add('scale-100');
            }, 10);
        }

        function closeAssignModal() {
            const modal = document.getElementById('assignModal');
            modal.classList.add('opacity-0');
            modal.querySelector('div').classList.remove('scale-100');
            modal.querySelector('div').classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Modal QR
        function openQrModal(bedId, bedNumber) {
            const qrUrl = `{{ url('/camas/scan') }}/${bedId}`;
            const printUrl = `/camas/${bedId}/qr/imprimir`;
            document.getElementById('qrBedNumber').innerText = '#' + bedNumber;
            document.getElementById('qrCodeContainer').innerHTML = '';
            
            // Generar QR con QRCode.js
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
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('div').classList.remove('scale-95');
                modal.querySelector('div').classList.add('scale-100');
            }, 10);
        }

        function closeQrModal() {
            const modal = document.getElementById('qrModal');
            modal.classList.add('opacity-0');
            modal.querySelector('div').classList.remove('scale-100');
            modal.querySelector('div').classList.add('scale-95');
            
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
