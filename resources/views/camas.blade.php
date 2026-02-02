@extends('layouts.app')

@section('content')
    <div class="mb-8 border-b border-slate-200 pb-6">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Dormitorios</span>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight uppercase leading-none">
                    <span class="text-red-700">Gestión</span> de Camas
                </h1>
                <p class="text-slate-500 font-medium text-xs mt-1">Control de ocupación y asignaciones</p>
            </div>
            
            <div class="flex items-center gap-3 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
                <div class="flex items-center px-2">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full mr-2 shadow-[0_0_8px_rgba(16,185,129,0.4)]"></span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wide">Disponible</span>
                </div>
                <div class="flex items-center px-2 border-l border-slate-100">
                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full mr-2 shadow-[0_0_8px_rgba(239,68,68,0.4)]"></span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wide">Ocupada</span>
                </div>
                <div class="flex items-center px-2 border-l border-slate-100">
                    <span class="w-2.5 h-2.5 bg-slate-400 rounded-full mr-2"></span>
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wide">Mantención</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($beds as $bed)
            <div class="relative bg-white rounded-xl shadow-sm border transition-all duration-300 hover:shadow-lg hover:-translate-y-1 flex flex-col h-full
                {{ $bed->status == 'available' ? 'border-slate-200' : ($bed->status == 'occupied' ? 'border-red-100 ring-1 ring-red-50' : 'border-slate-200 bg-slate-50') }}">
                
                <!-- Status Indicator Top Line -->
                <div class="absolute top-0 left-4 right-4 h-1 rounded-b-lg {{ $bed->status == 'available' ? 'bg-emerald-500' : ($bed->status == 'occupied' ? 'bg-red-500' : 'bg-slate-400') }}"></div>
                
                <div class="p-5 flex-grow flex flex-col">
                    <!-- Header Card -->
                    <div class="flex justify-between items-start mt-2 mb-4">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Cama N°</span>
                            <h2 class="text-4xl font-black text-slate-800 leading-none">{{ $bed->number }}</h2>
                        </div>
                        <div class="flex items-center justify-center w-10 h-10 rounded-full 
                            {{ $bed->status == 'available' ? 'bg-emerald-50 text-emerald-600' : ($bed->status == 'occupied' ? 'bg-red-50 text-red-600' : 'bg-slate-200 text-slate-500') }}">
                            <i class="fas fa-bed text-lg"></i>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-grow">
                        @if($bed->status == 'occupied' && $bed->currentAssignment)
                            <div class="space-y-3">
                                <!-- User Info -->
                                <div class="bg-red-50/50 rounded-lg p-3 border border-red-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-white border border-red-100 text-red-600 flex items-center justify-center font-bold text-xs shadow-sm">
                                            {{ substr($bed->currentAssignment->user->name, 0, 1) }}{{ substr($bed->currentAssignment->user->last_name_paternal, 0, 1) }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-[10px] font-bold text-red-400 uppercase tracking-wide mb-0.5">Ocupante</p>
                                            <p class="font-bold text-slate-800 text-sm truncate leading-tight">{{ $bed->currentAssignment->user->name }} {{ $bed->currentAssignment->user->last_name_paternal }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Time Info -->
                                <div class="flex items-center gap-2 text-xs text-slate-500 px-1">
                                    <i class="fas fa-clock text-slate-400"></i>
                                    <span>Ingreso: <span class="font-bold text-slate-700">{{ $bed->currentAssignment->assigned_at->format('H:i') }}</span> hrs</span>
                                </div>

                                <!-- Comments / Notes -->
                                @if($bed->currentAssignment->notes)
                                    <div class="relative bg-amber-50 rounded-lg p-3 border border-amber-100 mt-2">
                                        <i class="fas fa-quote-left absolute top-2 left-2 text-amber-200 text-xs"></i>
                                        <p class="text-xs text-amber-800 italic pl-4 leading-relaxed line-clamp-3">
                                            "{{ $bed->currentAssignment->notes }}"
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @elseif($bed->status == 'available')
                            <div class="h-full flex flex-col items-center justify-center text-center py-6 opacity-60">
                                <div class="w-12 h-1 bg-slate-100 rounded-full mb-3"></div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Disponible</p>
                            </div>
                        @else
                            <div class="h-full flex flex-col items-center justify-center text-center py-6">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Fuera de Servicio</p>
                                <p class="text-[10px] text-slate-400 mt-1">{{ $bed->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Footer / Actions -->
                    <div class="mt-5 pt-4 border-t border-slate-50">
                        @if($bed->status == 'available')
                            <div class="grid grid-cols-1 {{ auth()->user()->role === 'super_admin' ? 'sm:grid-cols-2' : '' }} gap-2">
                                <button onclick="openAssignModal('{{ $bed->id }}', '{{ $bed->number }}')" 
                                    class="group w-full bg-slate-900 hover:bg-emerald-600 text-white font-bold py-2.5 px-4 rounded-lg text-xs transition-all shadow-md hover:shadow-lg flex items-center justify-center uppercase tracking-wider">
                                    <span>Asignar Cama</span>
                                    <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                                </button>

                                @if(auth()->user()->role === 'super_admin')
                                    <form action="{{ route('beds.maintenance', $bed->id) }}" method="POST" onsubmit="return confirm('¿Marcar cama #{{ $bed->number }} en mantención?');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="group w-full bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 font-bold py-2.5 px-4 rounded-lg text-xs transition-all shadow-sm hover:shadow flex items-center justify-center uppercase tracking-wider">
                                            <i class="fas fa-screwdriver-wrench mr-2 text-slate-400 group-hover:text-slate-600"></i>
                                            <span>Mantención</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @elseif($bed->status == 'occupied')
                            @if($bed->currentAssignment)
                                <form action="{{ route('beds.release', $bed->currentAssignment->id) }}" method="POST" onsubmit="return confirm('¿Liberar esta cama?');">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="release" value="1">
                                    <button type="submit" class="group w-full bg-white hover:bg-red-50 text-slate-600 hover:text-red-600 border border-slate-200 hover:border-red-200 font-bold py-2.5 px-4 rounded-lg text-xs transition-all shadow-sm hover:shadow flex items-center justify-center uppercase tracking-wider">
                                        <i class="fas fa-right-from-bracket mr-2 group-hover:-translate-x-1 transition-transform"></i>
                                        <span>Liberar</span>
                                    </button>
                                </form>
                            @else
                                <div class="w-full bg-red-50 text-red-400 font-bold py-2 px-4 rounded-lg text-[10px] text-center uppercase tracking-wide border border-red-100 cursor-help" title="Error de integridad: Cama ocupada sin asignación">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Error Datos
                                </div>
                            @endif
                        @else
                            @if(auth()->user()->role === 'super_admin')
                                <form action="{{ route('beds.available', $bed->id) }}" method="POST" onsubmit="return confirm('¿Habilitar cama #{{ $bed->number }} como disponible?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="group w-full bg-white hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 border border-slate-200 hover:border-emerald-200 font-bold py-2.5 px-4 rounded-lg text-xs transition-all shadow-sm hover:shadow flex items-center justify-center uppercase tracking-wider">
                                        <i class="fas fa-check mr-2 text-emerald-500"></i>
                                        <span>Habilitar</span>
                                    </button>
                                </form>
                            @else
                                <button disabled class="w-full bg-slate-100 text-slate-400 font-bold py-2.5 px-4 rounded-lg text-xs cursor-not-allowed uppercase tracking-wider flex items-center justify-center">
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
                            <select name="user_id" class="w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-slate-50 transition-colors cursor-pointer hover:bg-white" required>
                                <option value="">Seleccione voluntario...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->last_name_paternal }}</option>
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
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('assignModal');
            if (event.target == modal) {
                closeAssignModal();
            }
        }
        
        // Cerrar con Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeAssignModal();
            }
        });
    </script>
@endsection
