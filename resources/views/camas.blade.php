@extends('layouts.modern')

@section('title', 'Gestión de Camas - ' . branding()->nombre_empresa)
@section('page-title', 'Gestión de Camas')

@section('content')
    <x-ui.page-header title="Gestión de Camas" subtitle="Control de ocupación y asignaciones en tiempo real" icon="fas fa-bed" iconVariant="emerald" />

    {{-- Métricas Compactas --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card-base p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-caption text-slate-500 dark:text-slate-400 mb-1">Total</p>
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $totalBeds }}</p>
                </div>
                <div class="icon-box icon-box-slate icon-box-md">
                    <i class="fas fa-bed"></i>
                </div>
            </div>
        </div>
        <div class="card-base p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-caption text-slate-500 dark:text-slate-400 mb-1">Ocupadas</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $occupiedBeds }}</p>
                </div>
                <div class="icon-box icon-box-red icon-box-md">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
        <div class="card-base p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-caption text-slate-500 dark:text-slate-400 mb-1">Disponibles</p>
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $availableBeds }}</p>
                </div>
                <div class="icon-box icon-box-emerald icon-box-md">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="card-base p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-caption text-slate-500 dark:text-slate-400 mb-1">Mantención</p>
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $maintenanceBeds }}</p>
                </div>
                <div class="icon-box icon-box-amber icon-box-md">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros Rápidos --}}
    <div class="card-base p-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Filtrar:</span>
            <a href="{{ route('camas') }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <i class="fas fa-list mr-2"></i>Todas
            </a>
            <a href="{{ route('camas', ['status' => 'occupied']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request('status') === 'occupied' ? 'bg-red-600 text-white' : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/30' }}">
                <i class="fas fa-user mr-2"></i>Ocupadas
            </a>
            <a href="{{ route('camas', ['status' => 'available']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request('status') === 'available' ? 'bg-emerald-600 text-white' : 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200 dark:hover:bg-emerald-900/30' }}">
                <i class="fas fa-check-circle mr-2"></i>Disponibles
            </a>
            <a href="{{ route('camas', ['status' => 'maintenance']) }}" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request('status') === 'maintenance' ? 'bg-amber-600 text-white' : 'bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/30' }}">
                <i class="fas fa-tools mr-2"></i>Mantención
            </a>
        </div>
    </div>

    <!-- Beds Grid - Modern Professional Design -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
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
            
            <div class="relative bg-gradient-to-br {{ $cardGradient }} rounded-2xl shadow-sm {{ $borderColor }} border overflow-hidden flex flex-col h-full transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group">
                
                <!-- Status Indicator Top Line -->
                <div class="absolute top-0 left-4 right-4 h-1 rounded-b-lg {{ $bed->status == 'available' ? 'bg-emerald-500' : ($bed->status == 'occupied' ? 'bg-red-500' : 'bg-slate-400') }}"></div>
                
                <div class="p-5 flex-grow flex flex-col">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <!-- Bed Number Badge -->
                            <div class="relative">
                                <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-900 border-2 border-{{ $statusColor }}-200 shadow-lg flex items-center justify-center">
                                    <span class="text-2xl font-bold text-{{ $statusColor }}-600">{{ $bed->name ?? $bed->number }}</span>
                                </div>
                                @if($isOccupied)
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center border-2 border-white shadow-md">
                                        <i class="fas fa-user text-[10px] text-white"></i>
                                    </div>
                                @elseif($isAvailable)
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-emerald-500 rounded-full flex items-center justify-center border-2 border-white shadow-md">
                                        <i class="fas fa-check text-[10px] text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Cama N°</span>
                                <div class="flex items-center gap-2">
                                    @if($isAvailable)
                                        <span class="text-xs font-bold text-emerald-600 bg-emerald-100 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full uppercase">Libre</span>
                                    @elseif($isOccupied)
                                        <span class="text-xs font-bold text-red-600 bg-red-100 dark:bg-red-900/30 px-2 py-0.5 rounded-full uppercase">Ocupada</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 bg-slate-200 dark:bg-slate-800 px-2 py-0.5 rounded-full uppercase">Mantención</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Icon -->
                        <div class="icon-box icon-box-{{ $statusColor }} icon-box-sm group-hover:scale-110 transition-transform">
                            <i class="fas fa-bed"></i>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-grow">
                        @if($isOccupied && $bed->currentAssignment)
                            @php
                                $firefighter = $bed->currentAssignment->firefighter;
                                $initials = strtoupper(substr($firefighter?->nombres ?? '', 0, 1) . substr($firefighter?->apellido_paterno ?? '', 0, 1));
                                $fullName = trim(($firefighter?->nombres ?? '') . ' ' . ($firefighter?->apellido_paterno ?? ''));
                            @endphp
                            
                            <!-- Occupant Card (MEJORADO) -->
                            <div class="card !bg-gradient-to-br !from-red-50 !to-red-100 dark:!from-red-900/30 dark:!to-red-900/20 !border-2 !border-red-300 dark:!border-red-700 mb-3 shadow-lg">
                                <div class="flex items-center gap-3 mb-3">
                                    @if($firefighter?->photo_path)
                                        <img src="{{ route('media', $firefighter->photo_path) }}" class="w-14 h-14 rounded-xl object-cover border-2 border-red-300 shadow-md" alt="{{ $fullName }}">
                                    @else
                                        <div class="w-14 h-14 rounded-xl bg-red-200 dark:bg-red-800 flex items-center justify-center border-2 border-red-300 dark:border-red-600 shadow-md">
                                            <span class="text-xl font-bold text-red-700 dark:text-red-300">{{ $initials ?: '?' }}</span>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wider mb-1">Ocupante</p>
                                        <p class="text-base font-bold text-red-900 dark:text-white truncate">{{ $fullName }}</p>
                                        @if($firefighter?->cargo_texto)
                                            <p class="text-xs text-red-700 dark:text-red-300">{{ $firefighter->cargo_texto }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Time Info (MEJORADO) -->
                                <div class="bg-white dark:bg-red-950 rounded-lg px-3 py-2.5 border border-red-200 dark:border-red-800">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-clock text-red-600 dark:text-red-400"></i>
                                            @php
                                                $startTime = $bed->currentAssignment->started_at ?? $bed->currentAssignment->assigned_at;
                                            @endphp
                                            @if($startTime)
                                                <span class="text-xs text-slate-600 dark:text-slate-300">Ingreso: <span class="font-bold text-slate-900 dark:text-white">{{ $startTime->format('H:i') }}</span> hrs</span>
                                            @else
                                                <span class="text-xs text-slate-400">Hora no disponible</span>
                                            @endif
                                        </div>
                                        @if($startTime)
                                            <span class="text-xs font-semibold text-red-600 dark:text-red-400">{{ $startTime->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            @if($bed->currentAssignment->notes)
                                <div class="card !bg-amber-50 dark:!bg-amber-900/20 !border-amber-200 dark:!border-amber-800">
                                    <div class="flex items-start gap-2">
                                        <i class="fas fa-sticky-note text-amber-500 mt-0.5 text-xs"></i>
                                        <p class="text-xs text-amber-800 dark:text-amber-200 leading-relaxed">{{ $bed->currentAssignment->notes }}</p>
                                    </div>
                                </div>
                            @endif
                            
                        @elseif($isAvailable)
                            <!-- Available State -->
                            <div class="h-full flex flex-col items-center justify-center py-8 text-center">
                                <div class="icon-box icon-box-emerald icon-box-xl mb-4 group-hover:scale-110 transition-transform shadow-lg">
                                    <i class="fas fa-bed text-3xl"></i>
                                </div>
                                <p class="text-sm font-bold text-emerald-700 uppercase">Cama Disponible</p>
                                <p class="text-xs text-slate-400 mt-1">Listo para asignar</p>
                            </div>
                        @else
                            <!-- Maintenance State -->
                            <div class="h-full flex flex-col items-center justify-center py-8 text-center">
                                <div class="icon-box icon-box-slate icon-box-xl mb-4 shadow-md">
                                    <i class="fas fa-tools text-3xl"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400 uppercase">Fuera de Servicio</p>
                                @if($bed->notes ?? $bed->description)
                                    <p class="text-xs text-slate-400 mt-1">{{ $bed->notes ?? $bed->description }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Actions Footer -->
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                        @if($isAvailable)
                            <div class="grid grid-cols-1 {{ auth()->user()->role === 'super_admin' ? 'sm:grid-cols-2' : '' }} gap-2">
                                <x-ui.button variant="primary" size="sm" icon="fas fa-user-plus" class="w-full" onclick="openAssignModal('{{ $bed->id }}', '{{ $bed->name ?? $bed->number }}')">
                                    Asignar
                                </x-ui.button>

                                @if(auth()->user()->role === 'super_admin')
                                    <x-ui.button variant="info" size="sm" icon="fas fa-qrcode" class="w-full" onclick="openQrModal('{{ $bed->id }}', '{{ $bed->name ?? $bed->number }}')">
                                        Ver QR
                                    </x-ui.button>
                                @endif
                            </div>
                            
                            @if(auth()->user()->role === 'super_admin')
                                <form action="{{ route('beds.maintenance', $bed->id) }}" method="POST" onsubmit="return confirm('¿Marcar cama #{{ $bed->name ?? $bed->number }} en mantención?');" class="m-0 mt-2">
                                    @csrf
                                    @method('PUT')
                                    <x-ui.button type="submit" variant="ghost" size="sm" icon="fas fa-tools" class="w-full">
                                        Mantención
                                    </x-ui.button>
                                </form>
                            @endif
                        @elseif($isOccupied)
                            @if($bed->currentAssignment)
                                <form action="{{ route('beds.release', $bed->currentAssignment->id) }}" method="POST" onsubmit="return confirm('¿Liberar esta cama?');">
                                    @csrf
                                    <input type="hidden" name="release" value="1">
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-[#1e293b]/10 px-3 py-2 text-xs bg-red-600 text-white hover:bg-red-700 shadow-sm">
                                        <i class="fas fa-right-from-bracket"></i>
                                        Liberar
                                    </button>
                                </form>
                            @else
                                <div class="w-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 font-bold py-3 px-4 rounded-xl text-[10px] text-center uppercase border border-red-200 dark:border-red-800 cursor-help" title="Error de integridad: Cama ocupada sin asignación">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Error Datos
                                </div>
                            @endif
                        @else
                            @if(auth()->user()->role === 'super_admin')
                                <form action="{{ route('beds.available', $bed->id) }}" method="POST" onsubmit="return confirm('¿Habilitar cama #{{ $bed->name ?? $bed->number }} como disponible?');" class="m-0">
                                    @csrf
                                    @method('PUT')
                                    <x-ui.button type="submit" variant="success" size="sm" icon="fas fa-check-circle" class="w-full">
                                        Habilitar
                                    </x-ui.button>
                                </form>
                            @else
                                <x-ui.button variant="ghost" size="sm" icon="fas fa-ban" class="w-full" disabled>
                                    No Disponible
                                </x-ui.button>
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
