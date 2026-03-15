{{-- Contenido del modal de Nueva Emergencia --}}
<div class="p-6">
    <div class="mb-4">
        <h3 class="text-lg font-bold text-white">Nueva Emergencia</h3>
        <p class="text-sm text-slate-400 mt-1">Registra una emergencia ocurrida durante la guardia</p>
    </div>

    <form id="emergency-create-form" method="POST" data-action-url="{{ route('admin.emergencies.store') }}">
        @csrf
        
        <div class="space-y-4 max-h-[55vh] overflow-y-auto pr-2">
            {{-- Clave de Emergencia --}}
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Clave de Emergencia *</label>
                <select name="emergency_key_id" required class="w-full px-3 py-2 border border-slate-700 rounded-lg bg-slate-950 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Seleccionar clave...</option>
                    @foreach($keys as $key)
                        <option value="{{ $key->id }}">{{ $key->code }} - {{ $key->description }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Hora de Salida --}}
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Hora de Salida *</label>
                <input type="datetime-local" name="dispatched_at" required value="{{ now()->format('Y-m-d\TH:i') }}"
                    class="w-full px-3 py-2 border border-slate-700 rounded-lg bg-slate-950 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            {{-- Hora de Llegada --}}
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Hora de Llegada</label>
                <input type="datetime-local" name="arrived_at"
                    class="w-full px-3 py-2 border border-slate-700 rounded-lg bg-slate-950 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            {{-- Unidades --}}
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Unidades</label>
                <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto p-2 border border-slate-700 rounded-lg bg-slate-950">
                    @foreach($units as $unit)
                        <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer hover:bg-slate-800 p-1 rounded">
                            <input type="checkbox" name="unit_ids[]" value="{{ $unit->id }}" class="rounded border-slate-600 bg-slate-800 text-sky-600 focus:ring-sky-500">
                            <span>{{ $unit->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- A Cargo --}}
            @if($onDutyUsers->isNotEmpty())
                <div>
                    <label class="block text-sm font-bold text-slate-300 mb-2">Oficial a Cargo</label>
                    <select name="officer_in_charge_firefighter_id" class="w-full px-3 py-2 border border-slate-700 rounded-lg bg-slate-950 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Sin asignar</option>
                        @foreach($onDutyUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->nombres }} {{ $user->apellido_paterno }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Detalles --}}
            <div>
                <label class="block text-sm font-bold text-slate-300 mb-2">Detalles del Llamado</label>
                <textarea name="details" rows="3" placeholder="Dirección, tipo de incidente, observaciones relevantes..."
                    class="w-full px-3 py-2 border border-slate-700 rounded-lg bg-slate-950 text-sm text-slate-300 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="closeEmergenciasModal()" 
                class="px-5 py-2.5 rounded-lg border border-slate-800 bg-slate-950 text-slate-100 font-bold text-sm hover:bg-slate-900 transition-colors uppercase">
                Cancelar
            </button>
            <button type="submit" 
                class="flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-5 rounded-lg text-sm uppercase tracking-wide transition-colors shadow-md">
                <i class="fas fa-floppy-disk"></i> Guardar Emergencia
            </button>
        </div>
    </form>
</div>
