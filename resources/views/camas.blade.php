@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Dormitorios</span>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight uppercase leading-none flex items-center gap-3">
                    <span class="text-red-700">Gestión</span> de Camas
                    <span class="text-xs font-black text-slate-400 bg-slate-100 px-2 py-1 rounded-lg">{{ $totalBeds ?? $beds->count() }} camas</span>
                </h1>
                <p class="text-slate-500 font-medium text-sm mt-2">Control de ocupación y asignaciones en tiempo real</p>
            </div>
            
            <!-- Status Legend -->
            <div class="flex items-center gap-2 bg-white p-2.5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center px-3 py-1.5 bg-emerald-50 rounded-lg border border-emerald-100">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full mr-2 shadow-[0_0_6px_rgba(16,185,129,0.5)]"></span>
                    <span class="text-[11px] font-black text-emerald-700 uppercase tracking-wide">Disponible</span>
                </div>
                <div class="flex items-center px-3 py-1.5 bg-red-50 rounded-lg border border-red-100">
                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full mr-2 shadow-[0_0_6px_rgba(239,68,68,0.5)]"></span>
                    <span class="text-[11px] font-black text-red-700 uppercase tracking-wide">Ocupada</span>
                </div>
                <div class="flex items-center px-3 py-1.5 bg-slate-100 rounded-lg border border-slate-200">
                    <span class="w-2.5 h-2.5 bg-slate-400 rounded-full mr-2"></span>
                    <span class="text-[11px] font-black text-slate-600 uppercase tracking-wide">Mantención</span>
                </div>
            </div>
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
                        : 'border-slate-200');
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
                                <div class="w-14 h-14 rounded-2xl bg-white border-2 border-{{ $statusColor }}-200 shadow-lg flex items-center justify-center">
                                    <span class="text-2xl font-black text-{{ $statusColor }}-600">{{ $bed->number }}</span>
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
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Cama N°</span>
                                <div class="flex items-center gap-2">
                                    @if($isAvailable)
                                        <span class="text-xs font-black text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full uppercase">Libre</span>
                                    @elseif($isOccupied)
                                        <span class="text-xs font-black text-red-600 bg-red-100 px-2 py-0.5 rounded-full uppercase">Ocupada</span>
                                    @else
                                        <span class="text-xs font-black text-slate-500 bg-slate-200 px-2 py-0.5 rounded-full uppercase">Mantención</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Icon -->
                        <div class="w-10 h-10 rounded-xl bg-{{ $statusColor }}-100 flex items-center justify-center text-{{ $statusColor }}-500 group-hover:scale-110 transition-transform">
                            <i class="fas fa-bed text-lg"></i>
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
                            
                            <!-- Occupant Card -->
                            <div class="bg-white rounded-xl p-4 border border-red-100 shadow-sm mb-3">
                                <div class="flex items-center gap-3">
                                    @if($firefighter?->photo_path)
                                        <img src="{{ url('media/' . ltrim($firefighter->photo_path, '/')) }}" class="w-12 h-12 rounded-xl object-cover border border-red-200 shadow-md" alt="{{ $fullName }}">
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-red-600 text-white flex items-center justify-center font-black text-sm shadow-md">
                                            {{ $initials ?: '?' }}
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[10px] font-black text-red-400 uppercase tracking-wide mb-0.5">Ocupante</p>
                                        <p class="font-bold text-slate-800 text-sm truncate leading-tight">{{ $fullName }}</p>
                                        @if($firefighter?->cargo_texto)
                                            <p class="text-[11px] text-slate-500 font-medium">{{ $firefighter->cargo_texto }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Time Info -->
                            <div class="flex items-center gap-2 text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-2 mb-3">
                                <i class="fas fa-clock text-slate-400"></i>
                                <span>Ingreso: <span class="font-bold text-slate-700">{{ $bed->currentAssignment->assigned_at->format('H:i') }}</span> hrs</span>
                                <span class="mx-1 text-slate-300">|</span>
                                <span class="text-slate-400">{{ $bed->currentAssignment->assigned_at->diffForHumans() }}</span>
                            </div>

                            <!-- Notes -->
                            @if($bed->currentAssignment->notes)
                                <div class="relative bg-amber-50 rounded-xl p-3 border border-amber-100">
                                    <div class="flex items-start gap-2">
                                        <i class="fas fa-sticky-note text-amber-400 mt-0.5 text-xs"></i>
                                        <p class="text-xs text-amber-800 leading-relaxed">{{ $bed->currentAssignment->notes }}</p>
                                    </div>
                                </div>
                            @endif
                            
                        @elseif($isAvailable)
                            <!-- Available State -->
                            <div class="h-full flex flex-col items-center justify-center py-8 text-center">
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-100 to-emerald-50 text-emerald-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform shadow-lg border-2 border-emerald-200">
                                    <i class="fas fa-bed text-3xl"></i>
                                </div>
                                <p class="text-sm font-black text-emerald-700 uppercase tracking-wide">Cama Disponible</p>
                                <p class="text-xs text-slate-400 mt-1">Listo para asignar</p>
                            </div>
                        @else
                            <!-- Maintenance State -->
                            <div class="h-full flex flex-col items-center justify-center py-8 text-center">
                                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-200 to-slate-100 text-slate-400 flex items-center justify-center mb-4 shadow-md border-2 border-slate-300">
                                    <i class="fas fa-tools text-3xl"></i>
                                </div>
                                <p class="text-sm font-black text-slate-500 uppercase tracking-wide">Fuera de Servicio</p>
                                @if($bed->description)
                                    <p class="text-xs text-slate-400 mt-1">{{ $bed->description }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Actions Footer -->
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        @if($isAvailable)
                            <div class="grid grid-cols-1 {{ auth()->user()->role === 'super_admin' ? 'sm:grid-cols-2' : '' }} gap-2">
                                <button onclick="openAssignModal('{{ $bed->id }}', '{{ $bed->number }}')" 
                                    class="group/btn w-full bg-slate-900 hover:bg-emerald-600 text-white font-bold py-3 px-4 rounded-xl text-xs transition-all shadow-md hover:shadow-lg flex items-center justify-center uppercase tracking-wider">
                                    <i class="fas fa-user-plus mr-2 group-hover/btn:scale-110 transition-transform"></i>
                                    <span>Asignar</span>
                                </button>

                                @if(auth()->user()->role === 'super_admin')
                                    <button onclick="openQrModal('{{ $bed->id }}', '{{ $bed->number }}')" 
                                        class="group/btn w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 px-4 rounded-xl text-xs transition-all shadow-md hover:shadow-lg flex items-center justify-center uppercase tracking-wider">
                                        <i class="fas fa-qrcode mr-2 group-hover/btn:scale-110 transition-transform"></i>
                                        <span>Ver QR</span>
                                    </button>
                                @endif
                            </div>
                            
                            @if(auth()->user()->role === 'super_admin')
                                <form action="{{ route('beds.maintenance', $bed->id) }}" method="POST" onsubmit="return confirm('¿Marcar cama #{{ $bed->number }} en mantención?');" class="m-0 mt-2">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="group/btn w-full bg-white hover:bg-slate-100 text-slate-500 border border-slate-200 font-bold py-3 px-4 rounded-xl text-xs transition-all shadow-sm hover:shadow flex items-center justify-center uppercase tracking-wider">
                                        <i class="fas fa-tools mr-2 text-slate-400 group-hover/btn:text-slate-600 transition-colors"></i>
                                        <span>Mantención</span>
                                    </button>
                                </form>
                            @endif
                        @elseif($isOccupied)
                            @if($bed->currentAssignment)
                                <form action="{{ route('beds.release', $bed->currentAssignment->id) }}" method="POST" onsubmit="return confirm('¿Liberar esta cama?');">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="release" value="1">
                                    <button type="submit" class="group/btn w-full bg-white hover:bg-red-50 text-slate-600 hover:text-red-600 border border-slate-200 hover:border-red-200 font-bold py-3 px-4 rounded-xl text-xs transition-all shadow-sm hover:shadow flex items-center justify-center uppercase tracking-wider">
                                        <i class="fas fa-right-from-bracket mr-2 group-hover/btn:scale-110 transition-transform"></i>
                                        <span>Liberar</span>
                                    </button>
                                </form>
                            @else
                                <div class="w-full bg-red-50 text-red-400 font-bold py-3 px-4 rounded-xl text-[10px] text-center uppercase tracking-wide border border-red-100 cursor-help" title="Error de integridad: Cama ocupada sin asignación">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Error Datos
                                </div>
                            @endif
                        @else
                            @if(auth()->user()->role === 'super_admin')
                                <form action="{{ route('beds.available', $bed->id) }}" method="POST" onsubmit="return confirm('¿Habilitar cama #{{ $bed->number }} como disponible?');" class="m-0">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="group/btn w-full bg-white hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 border border-slate-200 hover:border-emerald-200 font-bold py-3 px-4 rounded-xl text-xs transition-all shadow-sm hover:shadow flex items-center justify-center uppercase tracking-wider">
                                        <i class="fas fa-check-circle mr-2 text-emerald-500 group-hover/btn:scale-110 transition-transform"></i>
                                        <span>Habilitar</span>
                                    </button>
                                </form>
                            @else
                                <button disabled class="w-full bg-slate-100 text-slate-400 font-bold py-3 px-4 rounded-xl text-xs cursor-not-allowed uppercase tracking-wider flex items-center justify-center border border-slate-200">
                                    <i class="fas fa-ban mr-2"></i> No Disponible
                                </button>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Asignación -->
    <div id="assignModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-center justify-center transition-all duration-300 opacity-0">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-all duration-300">
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
            <div class="p-6">
                <form id="assignForm" method="POST" action="{{ route('beds.assign') }}">
                    @csrf
                    <input type="hidden" name="bed_id" id="modalBedId">
                    
                    <div class="mb-5">
                        <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide">Voluntario</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <select name="firefighter_id" class="w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 transition-colors cursor-pointer hover:bg-white" required>
                                <option value="">Seleccione voluntario...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->nombres }} {{ $user->apellido_paterno }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-slate-700 text-xs font-bold mb-2 uppercase tracking-wide">Comentario / Nota <span class="text-slate-400 font-normal normal-case">(Opcional)</span></label>
                        <div class="relative">
                            <i class="fas fa-comment-dots absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <textarea name="notes" class="w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 min-h-[80px] hover:bg-white transition-colors" placeholder="Ej: Se retira a las 07:00 hrs..."></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="closeAssignModal()" class="w-1/2 py-2.5 px-4 border border-slate-200 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-50 transition-colors uppercase tracking-wide">
                            Cancelar
                        </button>
                        <button type="submit" class="w-1/2 py-2.5 px-4 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-colors shadow-md uppercase tracking-wide flex items-center justify-center gap-2">
                            <span>Confirmar</span>
                            <i class="fas fa-check text-emerald-400"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal QR -->
    <div id="qrModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-center justify-center transition-all duration-300 opacity-0">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform scale-95 transition-all duration-300">
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
            <div class="p-6 text-center">
                <p class="text-sm text-slate-600 mb-4">Escanea este código con tu teléfono para asignarte esta cama</p>
                
                <!-- QR Code Container -->
                <div class="bg-slate-100 rounded-xl p-6 mb-4 inline-block">
                    <div id="qrCodeContainer" class="flex justify-center"></div>
                </div>
                
                <!-- URL -->
                <div class="bg-slate-50 rounded-lg p-3 mb-4">
                    <p class="text-xs text-slate-500 uppercase tracking-wider font-bold mb-1">URL</p>
                    <a id="qrUrl" href="#" target="_blank" class="text-sm text-cyan-600 font-mono break-all hover:text-cyan-500 transition-colors"></a>
                </div>

                <a id="qrPrintLink" href="#" target="_blank" class="w-full inline-flex items-center justify-center gap-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 px-6 rounded-xl text-xs transition-all uppercase tracking-wide mb-3">
                    <i class="fas fa-print"></i>
                    Imprimir
                </a>

                <button onclick="closeQrModal()" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 px-6 rounded-xl text-xs transition-all uppercase tracking-wide">
                    Cerrar
                </button>
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
            const printUrl = `{{ url('/camas') }}/${bedId}/qr/imprimir`;
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
