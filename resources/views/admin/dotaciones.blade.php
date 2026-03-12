@extends('layouts.modern')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
        <div class="flex items-center gap-4">
            <div class="icon-box icon-box-gradient-red icon-box-lg">
                <i class="fas fa-users-gear"></i>
            </div>
            <div>
                <h1 class="text-title-lg uppercase">Dotaciones</h1>
                <p class="text-body-sm">Asignación de personal a guardias</p>
            </div>
        </div>
    </div>

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
                <x-slot:header class="!bg-slate-900 !text-white !border-slate-800">
                    <div>
                        <h2 class="text-title-sm uppercase">{{ $guardia->name }}</h2>
                        <p class="text-caption flex items-center mt-1">
                            <i class="fas fa-users mr-2 opacity-50"></i> {{ $guardia->bomberos->count() }} Asignados
                        </p>
                    </div>
                </x-slot:header>

                <div class="p-4 space-y-4">
                    <form action="{{ route('admin.guardias.assign') }}" method="POST" class="space-y-3" onsubmit="return validateDotacionesForm(this)">
                        @csrf
                        <input type="hidden" name="guardia_id" value="{{ $guardia->id }}">

                        <div>
                            <label class="form-label">Voluntario</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input name="firefighter_id_display" autocomplete="off" data-dotaciones-volunteer-input data-guardia-id="{{ $guardia->id }}"
                                       class="form-input pl-9"
                                       placeholder="Buscar por nombre, apellido o RUT..." required>
                                <input type="hidden" name="firefighter_id" id="firefighter_id_input_{{ $guardia->id }}" required>

                                <div id="volunteer_dropdown_{{ $guardia->id }}" class="hidden absolute left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-lg z-20 max-h-60 overflow-auto"></div>
                            </div>
                            <div class="hidden text-xs text-red-700 font-bold mt-2" data-dotaciones-error data-guardia-id="{{ $guardia->id }}"></div>
                        </div>

                        <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-plus" class="w-full">
                            Asignar a Guardia
                        </x-ui.button>
                    </form>

                    <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
                        <h3 class="text-label mb-3">Personal asignado</h3>
                        <div class="space-y-2">
                            @forelse($guardia->bomberos as $user)
                                <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-2">
                                    <div>
                                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ $user->nombres }} {{ $user->apellido_paterno }}</p>
                                        @if($user->cargo_texto)
                                            <p class="text-caption">{{ $user->cargo_texto }}</p>
                                        @endif
                                        <p class="text-caption">
                                            {{ $user->es_jefe_guardia ? 'Jefe de Guardia' : 'Bombero' }}
                                        </p>
                                        <div class="flex gap-1 mt-1">
                                            @if($user->es_conductor)
                                                <span class="w-5 h-5 rounded-full icon-box icon-box-blue icon-box-xs" title="Conductor">
                                                    <i class="fas fa-car text-[9px]"></i>
                                                </span>
                                            @endif
                                            @if($user->es_operador_rescate)
                                                <span class="w-5 h-5 rounded-full icon-box icon-box-amber icon-box-xs" title="Operador de Rescate">R</span>
                                            @endif
                                            @if($user->es_asistente_trauma)
                                                <span class="w-5 h-5 rounded-full icon-box icon-box-red icon-box-xs" title="Asistente de Trauma">T</span>
                                            @endif
                                        </div>
                                    </div>
                                    <form action="{{ route('admin.guardias.unassign') }}" method="POST" onsubmit="return confirm('¿Quitar a este voluntario de la guardia?');">
                                        @csrf
                                        <input type="hidden" name="guardia_id" value="{{ $guardia->id }}">
                                        <input type="hidden" name="firefighter_id" value="{{ $user->id }}">
                                        <button type="submit" class="text-slate-400 hover:text-red-600 p-2 rounded-md hover:bg-white dark:bg-slate-900 transition-all" title="Quitar de guardia">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="text-body-sm text-slate-400">Sin personal asignado</div>
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
                btn.className = 'w-full text-left px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center justify-between';
                btn.addEventListener('click', function () {
                    setSelectedVolunteer(guardiaId, v);
                });

                const left = document.createElement('div');
                left.className = 'min-w-0';
                const name = document.createElement('div');
                name.className = 'text-sm font-bold text-slate-700 dark:text-slate-300 truncate';
                name.textContent = formatVolunteerLabel(v);
                left.appendChild(name);

                const right = document.createElement('div');
                right.className = 'text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-3 shrink-0';
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
