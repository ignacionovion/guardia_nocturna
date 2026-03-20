@extends('layouts.modern')

@section('content')
    <x-ui.page-header title="Dotaciones" subtitle="Asignación de personal a guardias" icon="fas fa-users-gear" iconVariant="red" />

    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if($errors->any())
        <x-ui.alert type="danger" icon="fas fa-exclamation-triangle" class="mb-6">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($guardias as $guardia)
            <x-ui.card>
                <x-slot:header class="!bg-[#c3cfdb] !border-[#9fb0c3]">
                    <div class="flex items-center justify-between w-full">
                        <div>
                            <h2 class="text-title-sm uppercase tracking-wide text-[#1e293b]">{{ $guardia->name }}</h2>
                            <p class="text-caption flex items-center mt-1 text-[#475569]">
                                <i class="fas fa-users mr-2"></i> {{ $guardia->bomberos->count() }} Asignados
                            </p>
                        </div>
                        <div class="h-10 w-10 rounded-full bg-[#9fb0c3] flex items-center justify-center">
                            <i class="fas fa-shield-halved text-[#1e293b]"></i>
                        </div>
                    </div>
                </x-slot:header>

                <div class="p-5 space-y-5">
                    <form action="{{ route('admin.guardias.assign') }}" method="POST" class="space-y-4" onsubmit="return validateDotacionesForm(this)">
                        @csrf
                        <input type="hidden" name="guardia_id" value="{{ $guardia->id }}">

                        <div class="space-y-2">
                            <label class="form-label">Buscar voluntario</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#475569]"></i>
                                <input name="firefighter_id_display" autocomplete="off" data-dotaciones-volunteer-input data-guardia-id="{{ $guardia->id }}"
                                       class="bg-[#e7eef5] border border-[#9fb0c3] text-[#1e293b] placeholder-[#475569] rounded-xl min-h-[44px] px-4 py-3 pl-10 text-sm focus:border-[#1e293b] focus:ring-2 focus:ring-[#1e293b]/10 focus:outline-none w-full"
                                       placeholder="Escribe nombre, apellido o RUT..." required>
                                <input type="hidden" name="firefighter_id" id="firefighter_id_input_{{ $guardia->id }}" required>

                                <div id="volunteer_dropdown_{{ $guardia->id }}" class="hidden absolute left-0 right-0 mt-1 bg-[#dde6ef] border border-[#9fb0c3] rounded-xl shadow-xl z-20 max-h-60 overflow-auto"></div>
                            </div>
                            <div class="hidden text-xs text-red-600 font-semibold mt-1" data-dotaciones-error data-guardia-id="{{ $guardia->id }}"></div>
                        </div>

                        <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-user-plus" class="w-full">
                            Asignar a {{ $guardia->name }}
                        </x-ui.button>
                    </form>

                    <div class="border-t border-[#9fb0c3] pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-label uppercase tracking-wide text-[#475569]">Personal asignado</h3>
                            @if($guardia->bomberos->count() > 0)
                                <x-ui.badge variant="success" size="xs">{{ $guardia->bomberos->count() }} activos</x-ui.badge>
                            @endif
                        </div>
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            @forelse($guardia->bomberos as $user)
                                <div class="flex items-center justify-between bg-[#e7eef5] border border-[#9fb0c3] rounded-xl p-3 hover:bg-[#c3cfdb] transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full bg-[#c3cfdb] flex items-center justify-center text-[#1e293b] font-bold text-sm">
                                            {{ substr($user->nombres, 0, 1) }}{{ substr($user->apellido_paterno, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-[#1e293b]">{{ $user->nombres }} {{ $user->apellido_paterno }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                @if($user->cargo_texto)
                                                    <span class="text-caption text-[#475569]">{{ $user->cargo_texto }}</span>
                                                @endif
                                                <span class="text-caption {{ $user->es_jefe_guardia ? 'text-amber-600 font-semibold' : 'text-[#475569]' }}">
                                                    {{ $user->es_jefe_guardia ? 'Jefe de Guardia' : 'Bombero' }}
                                                </span>
                                            </div>
                                            <div class="flex gap-1 mt-1.5">
                                                @if($user->es_conductor)
                                                    <x-ui.badge variant="info" size="xs" icon="fas fa-car">Cond</x-ui.badge>
                                                @endif
                                                @if($user->es_operador_rescate)
                                                    <x-ui.badge variant="warning" size="xs">Rescate</x-ui.badge>
                                                @endif
                                                @if($user->es_asistente_trauma)
                                                    <x-ui.badge variant="danger" size="xs">Trauma</x-ui.badge>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <form action="{{ route('admin.guardias.unassign') }}" method="POST" onsubmit="return confirm('¿Quitar a {{ $user->nombres }} {{ $user->apellido_paterno }} de esta guardia?');">
                                        @csrf
                                        <input type="hidden" name="guardia_id" value="{{ $guardia->id }}">
                                        <input type="hidden" name="firefighter_id" value="{{ $user->id }}">
                                        <button type="submit" class="text-[#475569] hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition-all" title="Quitar de guardia">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="text-center py-6 bg-[#e7eef5] rounded-xl border border-dashed border-[#9fb0c3]">
                                    <i class="fas fa-users-slash text-[#9fb0c3] text-2xl mb-2"></i>
                                    <p class="text-body-sm text-[#475569]">Sin personal asignado</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-ui.card>
        @endforeach
    </div>

    <script>
        function normalizeText(value) {
            return (value || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/ñ/g, 'n');
        }

        @php
            $dotacionesVolunteers = $volunteers->map(function ($v) {
                return [
                    'id' => $v->id,
                    'nombres' => $v->nombres,
                    'apellido_paterno' => $v->apellido_paterno,
                    'apellido_materno' => $v->apellido_materno,
                    'rut' => $v->rut,
                ];
            })->values();
        @endphp

        const DOTACIONES_VOLUNTEERS = @json($dotacionesVolunteers);

        const DOTACIONES_VOLUNTEERS_INDEX = DOTACIONES_VOLUNTEERS.map(v => {
            const label = [
                v.apellido_paterno,
                v.apellido_materno,
                v.nombres,
                v.rut,
            ].filter(Boolean).join(' ');

            return {
                ...v,
                label,
                haystack: normalizeText(label),
            };
        });

        function formatVolunteerLabel(v) {
            const last = [v.apellido_paterno, v.apellido_materno].filter(Boolean).join(' ');
            const main = [last, v.nombres].filter(Boolean).join(', ');
            const extra = [v.rut ? '- ' + v.rut : null].filter(Boolean).join(' ');
            return (main + (extra ? ' ' + extra : '')).trim();
        }

        function clearDotacionesError(guardiaId) {
            const el = document.querySelector('[data-dotaciones-error][data-guardia-id="' + guardiaId + '"]');
            if (!el) return;
            el.classList.add('hidden');
            el.textContent = '';
        }

        function showDotacionesError(guardiaId, message) {
            const el = document.querySelector('[data-dotaciones-error][data-guardia-id="' + guardiaId + '"]');
            if (!el) return;
            el.textContent = message;
            el.classList.remove('hidden');
        }

        function setSelectedVolunteer(guardiaId, volunteer) {
            const input = document.querySelector('[data-dotaciones-volunteer-input][data-guardia-id="' + guardiaId + '"]');
            const hiddenInput = document.getElementById('firefighter_id_input_' + guardiaId);
            const dropdown = document.getElementById('volunteer_dropdown_' + guardiaId);
            if (!input || !hiddenInput || !dropdown) return;

            input.value = formatVolunteerLabel(volunteer);
            hiddenInput.value = String(volunteer.id);
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            clearDotacionesError(guardiaId);
        }

        function renderDotacionesDropdown(guardiaId, items) {
            const dropdown = document.getElementById('volunteer_dropdown_' + guardiaId);
            if (!dropdown) return;

            if (!items || items.length === 0) {
                dropdown.classList.add('hidden');
                dropdown.innerHTML = '';
                return;
            }

            dropdown.innerHTML = '';
            for (const v of items) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left px-3 py-2 hover:bg-[#c3cfdb] transition flex items-center justify-between';
                btn.addEventListener('click', function () {
                    setSelectedVolunteer(guardiaId, v);
                });

                const left = document.createElement('div');
                left.className = 'min-w-0';
                const name = document.createElement('div');
                name.className = 'text-sm font-bold text-[#1e293b] truncate';
                name.textContent = formatVolunteerLabel(v);
                left.appendChild(name);

                const right = document.createElement('div');
                right.className = 'text-[10px] font-medium text-[#475569] ml-3 shrink-0';
                right.textContent = '#' + v.id;

                btn.appendChild(left);
                btn.appendChild(right);
                dropdown.appendChild(btn);
            }

            dropdown.classList.remove('hidden');
        }

        function attachDotacionesAutocomplete(input) {
            const guardiaId = input.getAttribute('data-guardia-id');
            const hiddenInput = document.getElementById('firefighter_id_input_' + guardiaId);
            const dropdown = document.getElementById('volunteer_dropdown_' + guardiaId);
            if (!guardiaId || !hiddenInput || !dropdown) return;

            let debounce = null;

            input.addEventListener('input', function () {
                hiddenInput.value = '';
                clearDotacionesError(guardiaId);

                const raw = input.value || '';
                const needle = normalizeText(raw);
                if (!needle || needle.length < 2) {
                    renderDotacionesDropdown(guardiaId, []);
                    return;
                }

                if (debounce) {
                    clearTimeout(debounce);
                }
                debounce = setTimeout(function () {
                    const results = [];
                    for (const v of DOTACIONES_VOLUNTEERS_INDEX) {
                        if (v.haystack.includes(needle)) {
                            results.push(v);
                            if (results.length >= 12) break;
                        }
                    }
                    renderDotacionesDropdown(guardiaId, results);
                }, 80);
            });

            input.addEventListener('focus', function () {
                const raw = input.value || '';
                const needle = normalizeText(raw);
                if (needle && needle.length >= 2 && !hiddenInput.value) {
                    const results = [];
                    for (const v of DOTACIONES_VOLUNTEERS_INDEX) {
                        if (v.haystack.includes(needle)) {
                            results.push(v);
                            if (results.length >= 12) break;
                        }
                    }
                    renderDotacionesDropdown(guardiaId, results);
                }
            });

            input.addEventListener('blur', function () {
                setTimeout(function () {
                    dropdown.classList.add('hidden');
                }, 150);
            });
        }

        function validateDotacionesForm(form) {
            const guardiaIdInput = form.querySelector('input[name="guardia_id"]');
            const guardiaId = guardiaIdInput ? guardiaIdInput.value : null;
            if (!guardiaId) return true;

            const hiddenInput = document.getElementById('firefighter_id_input_' + guardiaId);
            const displayInput = form.querySelector('[data-dotaciones-volunteer-input][data-guardia-id="' + guardiaId + '"]');

            if (!hiddenInput || !displayInput) {
                return true;
            }

            if (!hiddenInput.value) {
                showDotacionesError(guardiaId, 'Selecciona un voluntario de la lista.');
                displayInput.focus();
                return false;
            }

            clearDotacionesError(guardiaId);
            return true;
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-dotaciones-volunteer-input]').forEach(attachDotacionesAutocomplete);
        });
    </script>
@endsection
