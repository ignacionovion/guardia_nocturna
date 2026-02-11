@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-users-gear mr-3 text-red-700"></i> Dotaciones
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Asignación de personal a guardias</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-4 mb-6 rounded-r shadow-sm flex items-center" role="alert">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 mb-6 rounded-r shadow-sm" role="alert">
            <div class="flex items-center mb-2">
                <i class="fas fa-exclamation-triangle mr-2 text-xl"></i>
                <p class="font-bold">Error:</p>
            </div>
            <ul class="list-disc list-inside ml-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($guardias as $guardia)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-900 text-white p-4 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black tracking-tight uppercase">{{ $guardia->name }}</h2>
                        <p class="text-slate-400 text-xs mt-1 font-medium flex items-center">
                            <i class="fas fa-users mr-2 opacity-50"></i> {{ $guardia->bomberos->count() }} Asignados
                        </p>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <form action="{{ route('admin.guardias.assign') }}" method="POST" class="space-y-3" onsubmit="return validateDotacionesForm(this)">
                        @csrf
                        <input type="hidden" name="guardia_id" value="{{ $guardia->id }}">

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Voluntario</label>
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input name="firefighter_id_display" autocomplete="off" data-dotaciones-volunteer-input data-guardia-id="{{ $guardia->id }}"
                                       class="w-full text-sm border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 pl-9 py-2.5 bg-slate-50"
                                       placeholder="Buscar por nombre, apellido o RUT..." required>
                                <input type="hidden" name="firefighter_id" id="firefighter_id_input_{{ $guardia->id }}" required>

                                <div id="volunteer_dropdown_{{ $guardia->id }}" class="hidden absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-20 max-h-60 overflow-auto"></div>
                            </div>
                            <div class="hidden text-xs text-red-700 font-bold mt-2" data-dotaciones-error data-guardia-id="{{ $guardia->id }}"></div>
                        </div>

                        <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-white font-bold py-2.5 px-4 rounded-lg text-xs transition duration-150 shadow-md hover:shadow-lg flex items-center justify-center uppercase tracking-wider">
                            <i class="fas fa-plus mr-2"></i> Asignar a Guardia
                        </button>
                    </form>

                    <div class="border-t border-slate-100 pt-4">
                        <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Personal asignado</h3>
                        <div class="space-y-2">
                            @forelse($guardia->bomberos as $user)
                                <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg p-2">
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">{{ $user->nombres }} {{ $user->apellido_paterno }}</p>
                                        @if($user->cargo_texto)
                                            <p class="text-[11px] text-slate-600 font-black uppercase tracking-widest">{{ $user->cargo_texto }}</p>
                                        @endif
                                        <p class="text-[11px] text-slate-500 font-medium uppercase tracking-wide">
                                            {{ $user->es_jefe_guardia ? 'Jefe de Guardia' : 'Bombero' }}
                                        </p>
                                        <div class="flex gap-1 mt-1">
                                            @if($user->es_conductor)
                                                <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[9px] font-bold border border-blue-200" title="Conductor">
                                                    <i class="fas fa-car text-[9px]"></i>
                                                </span>
                                            @endif
                                            @if($user->es_operador_rescate)
                                                <span class="w-5 h-5 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[9px] font-bold border border-orange-200" title="Operador de Rescate">R</span>
                                            @endif
                                            @if($user->es_asistente_trauma)
                                                <span class="w-5 h-5 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-[9px] font-bold border border-red-200" title="Asistente de Trauma">T</span>
                                            @endif
                                        </div>
                                    </div>
                                    <form action="{{ route('admin.guardias.unassign') }}" method="POST" onsubmit="return confirm('¿Quitar a este voluntario de la guardia?');">
                                        @csrf
                                        <input type="hidden" name="guardia_id" value="{{ $guardia->id }}">
                                        <input type="hidden" name="firefighter_id" value="{{ $user->id }}">
                                        <button type="submit" class="text-slate-400 hover:text-red-600 p-2 rounded-md hover:bg-white transition-all" title="Quitar de guardia">
                                            <i class="fas fa-user-minus"></i>
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="text-sm text-slate-400">Sin personal asignado</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
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
                btn.className = 'w-full text-left px-3 py-2 hover:bg-slate-50 transition flex items-center justify-between';
                btn.addEventListener('click', function () {
                    setSelectedVolunteer(guardiaId, v);
                });

                const left = document.createElement('div');
                left.className = 'min-w-0';
                const name = document.createElement('div');
                name.className = 'text-sm font-bold text-slate-700 truncate';
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
