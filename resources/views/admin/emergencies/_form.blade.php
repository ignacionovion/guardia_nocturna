@php
    $selectedUnitIds = old('unit_ids', isset($emergency) ? $emergency->units->pluck('id')->toArray() : []);
    $selectedKeyId = old('emergency_key_id', isset($emergency) ? $emergency->emergency_key_id : null);
    $selectedOfficerId = old('officer_in_charge_firefighter_id', isset($emergency) ? ($emergency->officer_in_charge_firefighter_id ?? null) : null);

    $shiftLabel = $shift ? ('Turno activo #' . $shift->id . ' • ' . optional($shift->date)->format('d-m-Y')) : 'Sin turno activo detectado';
@endphp

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-lg border-2 border-slate-200 overflow-hidden">
            <div class="bg-slate-900 px-5 py-4 border-b-4 border-red-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-white font-bold tracking-wide">EMERGENCIAS CONCURRIDAS</h2>
                        <p class="text-slate-300 text-xs mt-0.5">{{ $shiftLabel }}</p>
                    </div>
                    <div class="bg-red-700 text-white px-3 py-1 rounded-lg text-xs font-bold">FICHA</div>
                </div>
            </div>

            <div class="p-5">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Clave</label>

                        <input type="hidden" name="emergency_key_id" id="emergency_key_id" value="{{ $selectedKeyId }}">

                        <button type="button" id="btn-pick-key" class="w-full text-left px-4 py-3 border-2 border-slate-200 rounded-xl bg-white hover:bg-slate-50 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-bold text-slate-900" id="key-code">{{ $selectedKeyId ? optional($keys->firstWhere('id', (int)$selectedKeyId))->code : 'Seleccionar clave...' }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5" id="key-desc">{{ $selectedKeyId ? \Illuminate\Support\Str::limit(optional($keys->firstWhere('id', (int)$selectedKeyId))->description, 60) : 'Usa el buscador para encontrar la clave.' }}</div>
                                </div>
                                <i class="fas fa-magnifying-glass text-slate-400 mt-1"></i>
                            </div>
                        </button>

                        <div class="text-[11px] text-slate-500 mt-1">Búsqueda rápida (por código o descripción).</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">H. salida</label>
                            <input type="datetime-local" name="dispatched_at"
                                value="{{ old('dispatched_at', isset($emergency) && $emergency->dispatched_at ? $emergency->dispatched_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                                class="w-full px-3 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">H. llegada</label>
                            <input type="datetime-local" name="arrived_at"
                                value="{{ old('arrived_at', isset($emergency) && $emergency->arrived_at ? $emergency->arrived_at->format('Y-m-d\TH:i') : '') }}"
                                class="w-full px-3 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Unidades</label>

                        <button type="button" id="btn-pick-units" class="w-full text-left px-4 py-3 border-2 border-slate-200 rounded-xl bg-white hover:bg-slate-50 transition">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-bold text-slate-900">Seleccionar unidades</div>
                                    <div class="text-xs text-slate-500 mt-0.5"><span id="units-count">{{ count($selectedUnitIds) }}</span> seleccionada(s)</div>
                                </div>
                                <i class="fas fa-magnifying-glass text-slate-400 mt-1"></i>
                            </div>
                        </button>

                        <div class="mt-2 flex flex-wrap gap-1" id="selected-units-chips">
                            @foreach($units as $unit)
                                @if(in_array($unit->id, $selectedUnitIds))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">{{ $unit->name }}</span>
                                @endif
                            @endforeach
                        </div>

                        @foreach($selectedUnitIds as $id)
                            <input type="hidden" name="unit_ids[]" value="{{ $id }}" class="unit-hidden">
                        @endforeach
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">A cargo</label>
                        
                        <!-- Custom Searchable Dropdown -->
                        <div class="relative" id="officer-select-container">
                            <input type="hidden" name="officer_in_charge_firefighter_id" id="officer_in_charge_firefighter_id" value="{{ $selectedOfficerId }}">
                            
                            <!-- Search Input -->
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="text" id="officer-search-input"
                                    class="w-full text-sm border-2 border-slate-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 pl-9 pr-10 py-3 bg-white text-slate-700 placeholder:text-slate-500 cursor-pointer"
                                    placeholder="Buscar oficial..." autocomplete="off" readonly
                                    value="{{ $selectedOfficerId ? optional($onDutyUsers->firstWhere('id', $selectedOfficerId))->nombres . ' ' . optional($onDutyUsers->firstWhere('id', $selectedOfficerId))->apellido_paterno : 'Sin asignar' }}">
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            </div>
                            
                            <!-- Dropdown Menu - Fixed position to escape grid overflow -->
                            <div id="officer-dropdown" class="hidden fixed bg-white border border-slate-200 rounded-xl shadow-2xl z-[100] max-h-64 overflow-y-auto" style="width: inherit; min-width: 280px;">
                                <div class="p-2 sticky top-0 bg-white border-b border-slate-100">
                                    <input type="text" id="officer-filter-input" 
                                        class="w-full text-xs bg-slate-50 border-slate-200 rounded-lg px-3 py-2 text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        placeholder="Filtrar por nombre...">
                                </div>
                                <div id="officer-options-list" class="py-1">
                                    <div class="officer-option px-3 py-2.5 hover:bg-slate-50 cursor-pointer transition-colors flex items-center gap-3 {{ !$selectedOfficerId ? 'bg-blue-50' : '' }}"
                                         data-value=""
                                         data-search="sin asignar">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-xs font-bold">
                                            <i class="fas fa-minus"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-slate-700">Sin asignar</div>
                                        </div>
                                    </div>
                                    @foreach($onDutyUsers as $u)
                                        <div class="officer-option px-3 py-2.5 hover:bg-slate-50 cursor-pointer transition-colors flex items-center gap-3 {{ (string)$selectedOfficerId === (string)$u->id ? 'bg-blue-50' : '' }}"
                                             data-value="{{ $u->id }}"
                                             data-search="{{ strtolower(trim($u->nombres . ' ' . $u->apellido_paterno . ' ' . ($u->rut ?? ''))) }}">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold">
                                                {{ strtoupper(substr($u->nombres, 0, 1)) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium text-slate-700 truncate">
                                                    {{ $u->nombres }} {{ $u->apellido_paterno }}
                                                </div>
                                                @if($u->cargo_texto)
                                                    <div class="text-xs text-slate-500">{{ $u->cargo_texto }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="officer-no-results" class="hidden px-3 py-4 text-center text-xs text-slate-500">
                                    No se encontraron resultados
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-[11px] text-slate-500 mt-1">Solo personal en servicio (turno activo).</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 bg-slate-50 border border-slate-200 rounded-xl p-4">
            <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ayuda rápida</div>
            <div class="text-sm text-slate-600 space-y-1">
                <div><span class="font-semibold">Hora salida:</span> despacho de la emergencia.</div>
                <div><span class="font-semibold">Hora llegada:</span> llegada del carro al cuartel.</div>
                <div><span class="font-semibold">Unidades:</span> carros que acuden a la emergencia.</div>
                <div><span class="font-semibold">A cargo:</span> oficial responsable (en servicio).</div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-3">
        <div class="bg-white rounded-2xl shadow-lg border-2 border-slate-200 overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Detalles del llamado</h3>
            </div>
            <div class="p-6">
                <textarea name="details" rows="14"
                    placeholder="Describe el llamado: dirección, tipo de incidente, observaciones relevantes, personas involucradas, etc."
                    class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-slate-700 text-sm bg-white">{{ old('details', isset($emergency) ? $emergency->details : '') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('admin.emergencies.index') }}" class="px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold transition-colors">Cancelar</a>
            <button type="submit" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl shadow-md transition-all duration-200">
                <i class="fas fa-save mr-2"></i> Guardar
            </button>
        </div>
    </div>
</div>

<!-- Modal Claves -->
<div id="modal-keys" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/40" data-close="keys"></div>
    <div class="relative mx-auto mt-16 w-[95%] max-w-3xl bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 px-5 py-4 border-b-4 border-red-700 flex items-center justify-between">
            <div>
                <div class="text-white font-bold">Seleccionar Clave</div>
                <div class="text-slate-300 text-xs">Filtra por código o descripción</div>
            </div>
            <button type="button" class="text-slate-200 hover:text-white" data-close="keys"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 border-b border-slate-200">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-3.5 text-slate-400"></i>
                <input id="keys-search" type="text" placeholder="Buscar..." class="w-full pl-10 pr-3 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500">
            </div>
        </div>
        <div class="max-h-[60vh] overflow-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Código</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Descripción</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody id="keys-tbody" class="bg-white divide-y divide-slate-200">
                    @foreach($keys as $k)
                        <tr class="hover:bg-slate-50 transition" data-code="{{ strtolower($k->code) }}" data-desc="{{ strtolower($k->description) }}">
                            <td class="px-4 py-3 font-mono text-sm font-bold text-slate-900">{{ $k->code }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $k->description }}</td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold" data-pick-key="{{ $k->id }}" data-pick-code="{{ $k->code }}" data-pick-desc="{{ $k->description }}">Seleccionar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Unidades -->
<div id="modal-units" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/40" data-close="units"></div>
    <div class="relative mx-auto mt-16 w-[95%] max-w-3xl bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-slate-900 px-5 py-4 border-b-4 border-red-700 flex items-center justify-between">
            <div>
                <div class="text-white font-bold">Seleccionar Unidades</div>
                <div class="text-slate-300 text-xs">Marca una o más unidades</div>
            </div>
            <button type="button" class="text-slate-200 hover:text-white" data-close="units"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-4 border-b border-slate-200">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-3.5 text-slate-400"></i>
                <input id="units-search" type="text" placeholder="Buscar..." class="w-full pl-10 pr-3 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-100 focus:border-blue-500">
            </div>
        </div>
        <div class="max-h-[60vh] overflow-auto p-4" id="units-list">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($units as $u)
                    <label class="unit-item flex items-start gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition" data-name="{{ strtolower($u->name) }}" data-desc="{{ strtolower($u->description ?? '') }}">
                        <input type="checkbox" class="mt-1 unit-checkbox w-5 h-5 rounded border-slate-300 text-blue-600" value="{{ $u->id }}" {{ in_array($u->id, $selectedUnitIds) ? 'checked' : '' }}>
                        <div>
                            <div class="font-bold text-slate-900">{{ $u->name }}</div>
                            @if($u->description)
                                <div class="text-xs text-slate-500">{{ $u->description }}</div>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
            <button type="button" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-white" data-close="units">Cancelar</button>
            <button type="button" id="btn-units-apply" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold">Aplicar</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const emergencyForm = document.getElementById('emergency-form') || document.currentScript?.closest('form');
        const modalKeys = document.getElementById('modal-keys');
        const modalUnits = document.getElementById('modal-units');

        const btnPickKey = document.getElementById('btn-pick-key');
        const keySearch = document.getElementById('keys-search');
        const keysTbody = document.getElementById('keys-tbody');

        const inputKeyId = document.getElementById('emergency_key_id');
        const keyCode = document.getElementById('key-code');
        const keyDesc = document.getElementById('key-desc');

        const btnPickUnits = document.getElementById('btn-pick-units');
        const unitsSearch = document.getElementById('units-search');
        const btnUnitsApply = document.getElementById('btn-units-apply');
        const selectedUnitsChips = document.getElementById('selected-units-chips');
        const unitsCount = document.getElementById('units-count');

        function openModal(modal) {
            modal.classList.remove('hidden');
        }

        function closeModal(modal) {
            modal.classList.add('hidden');
        }

        document.querySelectorAll('[data-close="keys"]').forEach(el => {
            el.addEventListener('click', () => closeModal(modalKeys));
        });
        document.querySelectorAll('[data-close="units"]').forEach(el => {
            el.addEventListener('click', () => closeModal(modalUnits));
        });

        btnPickKey?.addEventListener('click', function () {
            openModal(modalKeys);
            setTimeout(() => keySearch?.focus(), 50);
        });

        keySearch?.addEventListener('input', function () {
            const q = (this.value || '').toLowerCase().trim();
            const rows = keysTbody.querySelectorAll('tr');
            rows.forEach(row => {
                const code = row.getAttribute('data-code') || '';
                const desc = row.getAttribute('data-desc') || '';
                row.style.display = (!q || code.includes(q) || desc.includes(q)) ? '' : 'none';
            });
        });

        keysTbody?.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-pick-key]');
            if (!btn) return;

            const id = btn.getAttribute('data-pick-key');
            const code = btn.getAttribute('data-pick-code');
            const desc = btn.getAttribute('data-pick-desc');

            inputKeyId.value = id;
            keyCode.textContent = code;
            keyDesc.textContent = desc;

            closeModal(modalKeys);
        });

        btnPickUnits?.addEventListener('click', function () {
            openModal(modalUnits);
            setTimeout(() => unitsSearch?.focus(), 50);
        });

        unitsSearch?.addEventListener('input', function () {
            const q = (this.value || '').toLowerCase().trim();
            document.querySelectorAll('.unit-item').forEach(item => {
                const name = item.getAttribute('data-name') || '';
                const desc = item.getAttribute('data-desc') || '';
                item.style.display = (!q || name.includes(q) || desc.includes(q)) ? '' : 'none';
            });
        });

        function syncHiddenUnits(ids) {
            if (!emergencyForm) return;
            emergencyForm.querySelectorAll('input.unit-hidden').forEach(el => el.remove());
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'unit_ids[]';
                input.value = id;
                input.className = 'unit-hidden';
                emergencyForm.appendChild(input);
            });
        }

        function renderUnitChips(ids) {
            selectedUnitsChips.innerHTML = '';
            const map = new Map();
            document.querySelectorAll('.unit-checkbox').forEach(cb => {
                const label = cb.closest('label');
                const name = label?.querySelector('.font-bold')?.textContent?.trim();
                if (name) map.set(cb.value, name);
            });

            ids.forEach(id => {
                const name = map.get(String(id));
                if (!name) return;
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100';
                chip.textContent = name;
                selectedUnitsChips.appendChild(chip);
            });

            if (unitsCount) {
                unitsCount.textContent = String(ids.length);
            }
        }

        btnUnitsApply?.addEventListener('click', function () {
            const ids = Array.from(document.querySelectorAll('.unit-checkbox:checked')).map(cb => cb.value);
            syncHiddenUnits(ids);
            renderUnitChips(ids);
            closeModal(modalUnits);
        });

        emergencyForm?.addEventListener('submit', function () {
            const ids = Array.from(document.querySelectorAll('.unit-checkbox:checked')).map(cb => cb.value);
            syncHiddenUnits(ids);
        });

        if (emergencyForm) {
            const idsInit = Array.from(emergencyForm.querySelectorAll('input[name="unit_ids[]"]')).map(el => el.value);
            renderUnitChips(idsInit);
        }

        // Custom Officer Dropdown
        const officerSearchInput = document.getElementById('officer-search-input');
        const officerDropdown = document.getElementById('officer-dropdown');
        const officerFilterInput = document.getElementById('officer-filter-input');
        const officerOptionsList = document.getElementById('officer-options-list');
        const officerNoResults = document.getElementById('officer-no-results');
        const officerHiddenInput = document.getElementById('officer_in_charge_firefighter_id');
        const officerContainer = document.getElementById('officer-select-container');
        
        if (officerSearchInput && officerDropdown) {
            let isOfficerOpen = false;

            function openOfficerDropdown() {
                isOfficerOpen = true;
                // Position dropdown below the input using fixed positioning
                const rect = officerSearchInput.getBoundingClientRect();
                officerDropdown.style.top = (rect.bottom + 4) + 'px';
                officerDropdown.style.left = rect.left + 'px';
                officerDropdown.style.width = rect.width + 'px';
                officerDropdown.classList.remove('hidden');
                officerFilterInput.focus();
                officerFilterInput.value = '';
                filterOfficerOptions('');
            }

            function closeOfficerDropdown() {
                isOfficerOpen = false;
                officerDropdown.classList.add('hidden');
            }

            officerSearchInput.addEventListener('click', function(e) {
                e.stopPropagation();
                if (!isOfficerOpen) {
                    openOfficerDropdown();
                }
            });

            document.addEventListener('click', function(e) {
                if (!officerContainer.contains(e.target)) {
                    closeOfficerDropdown();
                }
            });

            officerFilterInput.addEventListener('input', function() {
                filterOfficerOptions(this.value.toLowerCase());
            });

            function filterOfficerOptions(query) {
                const options = officerOptionsList.querySelectorAll('.officer-option');
                let visibleCount = 0;

                options.forEach(function(option) {
                    const searchData = option.getAttribute('data-search') || '';
                    if (searchData.includes(query)) {
                        option.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        option.classList.add('hidden');
                    }
                });

                if (visibleCount === 0) {
                    officerNoResults.classList.remove('hidden');
                } else {
                    officerNoResults.classList.add('hidden');
                }
            }

            officerOptionsList.addEventListener('click', function(e) {
                const option = e.target.closest('.officer-option');
                if (!option) return;

                const value = option.getAttribute('data-value');
                const text = option.querySelector('.text-sm').textContent.trim();

                officerHiddenInput.value = value;
                officerSearchInput.value = text;
                
                // Update visual selection
                officerOptionsList.querySelectorAll('.officer-option').forEach(opt => {
                    opt.classList.remove('bg-blue-50');
                });
                option.classList.add('bg-blue-50');
                
                closeOfficerDropdown();
            });

            officerFilterInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeOfficerDropdown();
                    officerSearchInput.focus();
                }
            });
        }
    });
</script>
