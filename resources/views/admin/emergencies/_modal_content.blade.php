{{-- Contenido del modal de Emergencias --}}
<div class="p-6">
    <div class="mb-4">
        <form action="{{ route('admin.emergencies.modal') }}" method="GET" class="relative" id="emergency-search-form">
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Buscar por clave o detalle del llamado..."
                        class="w-full pl-11 pr-4 py-2.5 border border-slate-700 rounded-lg bg-slate-950 text-sm text-slate-300 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                @if(request('search'))
                    <a href="{{ route('admin.emergencies.modal') }}" class="text-slate-400 hover:text-slate-300 transition-colors">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <button type="submit" class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-sm uppercase tracking-wide transition-colors">
                    Buscar
                </button>
            </div>
        </form>
    </div>

    @if($emergencies->isEmpty())
        <div class="text-center py-12">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-800 flex items-center justify-center">
                <i class="fas fa-truck-medical text-slate-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-300 mb-2">No hay emergencias registradas</h3>
            <p class="text-sm text-slate-500">{{ request('search') ? 'No se encontraron resultados para tu búsqueda' : 'Registra una emergencia para comenzar el historial' }}</p>
        </div>
    @else
        <div class="overflow-x-auto max-h-[55vh] overflow-y-auto">
            <table class="min-w-full divide-y divide-slate-800">
                <thead class="bg-slate-950 sticky top-0 z-10">
                    <tr class="text-xs font-bold uppercase tracking-wide text-slate-400">
                        <th scope="col" class="px-4 py-3 text-left">Clave</th>
                        <th scope="col" class="px-4 py-3 text-left">H. salida</th>
                        <th scope="col" class="px-4 py-3 text-left">H. llegada</th>
                        <th scope="col" class="px-4 py-3 text-left">Unidades</th>
                        <th scope="col" class="px-4 py-3 text-left">A cargo</th>
                        <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($emergencies as $emergency)
                        <tr class="hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 align-top">
                                <div class="text-sm font-bold text-white">{{ $emergency->key?->code ?? '-' }}</div>
                                <div class="text-sm text-slate-400">{{ \Illuminate\Support\Str::limit($emergency->key?->description ?? '', 40) }}</div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="text-sm text-slate-300">{{ $emergency->departure_time ? \Carbon\Carbon::parse($emergency->departure_time)->format('H:i') : '-' }}</div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="text-sm text-slate-300">{{ $emergency->arrival_time ? \Carbon\Carbon::parse($emergency->arrival_time)->format('H:i') : '-' }}</div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="text-sm text-slate-300">{{ $emergency->units->pluck('name')->join(', ') ?: '-' }}</div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="text-sm text-slate-300">{{ $emergency->in_charge ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 align-top text-right">
                                <a href="{{ route('admin.emergencies.edit', $emergency) }}" 
                                    class="inline-flex items-center gap-1 text-sm text-sky-400 hover:text-sky-300 transition-colors">
                                    <i class="fas fa-edit text-xs"></i>
                                    <span>Editar</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($emergencies->hasPages())
            <div class="mt-4 flex justify-center">
                {{ $emergencies->links() }}
            </div>
        @endif
    @endif

    <div class="mt-6 flex justify-between items-center">
        <button type="button" onclick="loadEmergencyCreateForm()" 
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-sm uppercase tracking-wide transition-colors shadow-md">
            <i class="fas fa-plus"></i>
            Nueva Emergencia
        </button>
        
        <button type="button" onclick="closeEmergenciasModal()" 
            class="px-5 py-2.5 rounded-lg border border-slate-800 bg-slate-950 text-slate-100 font-bold text-sm hover:bg-slate-900 transition-colors uppercase">
            Cerrar
        </button>
    </div>
</div>

<script>
    window.loadEmergencyCreateForm = async function() {
        const content = document.getElementById('emergenciasModalContent');
        if (!content) return;
        
        content.innerHTML = '<div class="flex items-center justify-center py-12"><i class="fas fa-spinner fa-spin text-slate-400 text-2xl"></i></div>';
        
        try {
            const response = await fetch('/admin/emergencies/create/modal', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (response.ok) {
                const html = await response.text();
                content.innerHTML = html;
            } else {
                content.innerHTML = '<div class="p-6 text-center text-slate-400">Error al cargar el formulario</div>';
            }
        } catch (error) {
            console.error('Error loading emergency create form:', error);
            content.innerHTML = '<div class="p-6 text-center text-slate-400">Error de conexión</div>';
        }
    }
</script>
