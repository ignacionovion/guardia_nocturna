    <div id="noveltyModal" class="fixed inset-0 bg-slate-900 bg-opacity-75 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative p-6 border w-full max-w-lg shadow-2xl rounded-xl {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-900 border-slate-800' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700' }}">
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border border-slate-800' : 'bg-blue-100' }} mb-4">
                    <i class="fas fa-pen-to-square {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-blue-300' : 'text-blue-600' }} text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-100' : 'text-slate-800 dark:text-white' }}">Registrar Novedad</h3>
                <p class="{{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-400' : 'text-slate-500 dark:text-slate-400' }} text-sm mt-1">Bitácora de la Guardia</p>
            </div>
            
            <form action="{{ route('novelties.store_web') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700 dark:text-slate-300' }}">Título</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full px-4 py-2.5 border {{ $errors->has('title') ? 'border-red-500' : ((Auth::check() && Auth::user()->role === 'guardia') ? 'border-slate-800' : 'border-slate-300 dark:border-slate-600') }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 text-slate-100 placeholder:text-slate-500 dark:text-slate-400' : 'bg-white dark:bg-slate-900 text-slate-900' }}" placeholder="Ej: Falla en carro B-3" required>
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700 dark:text-slate-300' }}">Tipo</label>
                    <select name="type" class="w-full px-4 py-2.5 border {{ $errors->has('type') ? 'border-red-500' : ((Auth::check() && Auth::user()->role === 'guardia') ? 'border-slate-800' : 'border-slate-300 dark:border-slate-600') }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 text-slate-100' : 'bg-white dark:bg-slate-900 text-slate-900' }}">
                        <option value="Informativa" {{ old('type') == 'Informativa' ? 'selected' : '' }}>Informativa</option>
                        <option value="Incidente" {{ old('type') == 'Incidente' ? 'selected' : '' }}>Incidente</option>
                        <option value="Mantención" {{ old('type') == 'Mantención' ? 'selected' : '' }}>Mantención</option>
                        <option value="Urgente" {{ old('type') == 'Urgente' ? 'selected' : '' }}>Urgente</option>
                        <option value="Permanente" {{ old('type') == 'Permanente' ? 'selected' : '' }}>Permanente (Todas las Guardias)</option>
                    </select>
                    <div class="mt-1 text-[10px] text-slate-500 dark:text-slate-400">
                        <i class="fas fa-info-circle mr-1"></i> Las novedades "Permanentes" son visibles para todas las guardias. Solo admin/capitán pueden eliminarlas.
                    </div>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700 dark:text-slate-300' }}">Descripción</label>
                    <textarea name="description" class="w-full px-4 py-2.5 border {{ $errors->has('description') ? 'border-red-500' : ((Auth::check() && Auth::user()->role === 'guardia') ? 'border-slate-800' : 'border-slate-300 dark:border-slate-600') }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-h-[100px] {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 text-slate-100 placeholder:text-slate-500 dark:text-slate-400' : 'bg-white dark:bg-slate-900 text-slate-900' }}" placeholder="Detalle de la novedad..." required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeNoveltyModal()" class="w-1/2 px-4 py-2.5 font-bold rounded-lg transition-colors uppercase text-sm {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 hover:bg-slate-900 text-slate-100 border border-slate-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200' }}">
                        Cancelar
                    </button>
                    <button type="submit" class="w-1/2 px-4 py-2.5 bg-blue-700 text-white font-bold rounded-lg hover:bg-blue-800 transition-colors shadow-md uppercase text-sm">
                        Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="yearCalendarModal" class="fixed inset-0 bg-slate-900/85 backdrop-blur-sm hidden overflow-y-auto h-full w-full z-50 p-3 sm:p-6">
        <div class="max-w-7xl mx-auto min-h-full flex items-start justify-center">
            <div class="w-full rounded-3xl border border-slate-800 bg-slate-900 shadow-2xl overflow-hidden my-4">
                <div class="sticky top-0 z-10 px-4 py-4 sm:px-6 bg-slate-900/95 backdrop-blur border-b border-slate-800 flex items-center justify-between gap-4">
                    <div>
                        <div class="text-[10px] font-bold text-emerald-300 uppercase tracking-[0.25em]">Calendario anual</div>
                        <div class="text-lg sm:text-2xl font-bold text-slate-100">Guardias {{ now()->year }}</div>
                    </div>
                    <button type="button" onclick="closeCalendarPopup()" class="w-10 h-10 rounded-2xl border border-slate-700 bg-slate-950 text-slate-300 hover:bg-slate-800 hover:text-white transition-colors flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @php
                    $yearGuardiaPalette = [
                        ['badge' => 'bg-blue-500/10 text-blue-200 border-blue-500/20', 'dot' => 'bg-blue-400'],
                        ['badge' => 'bg-emerald-500/10 text-emerald-200 border-emerald-500/20', 'dot' => 'bg-emerald-400'],
                        ['badge' => 'bg-amber-500/10 text-amber-200 border-amber-500/20', 'dot' => 'bg-amber-400'],
                        ['badge' => 'bg-purple-500/10 text-purple-200 border-purple-500/20', 'dot' => 'bg-purple-400'],
                        ['badge' => 'bg-rose-500/10 text-rose-200 border-rose-500/20', 'dot' => 'bg-rose-400'],
                        ['badge' => 'bg-cyan-500/10 text-cyan-200 border-cyan-500/20', 'dot' => 'bg-cyan-400'],
                        ['badge' => 'bg-orange-500/10 text-orange-200 border-orange-500/20', 'dot' => 'bg-orange-400'],
                        ['badge' => 'bg-fuchsia-500/10 text-fuchsia-200 border-fuchsia-500/20', 'dot' => 'bg-fuchsia-400'],
                    ];
                    $yearGuardias = \App\Models\Guardia::query()->orderBy('name')->get();
                    $yearGuardiaColors = $yearGuardias->mapWithKeys(function ($guardia) use ($yearGuardiaPalette) {
                        return [$guardia->id => $yearGuardiaPalette[$guardia->id % count($yearGuardiaPalette)]];
                    });
                    $yearCalendarDays = \App\Models\GuardiaCalendarDay::query()
                        ->with('guardia:id,name')
                        ->whereYear('date', now()->year)
                        ->get()
                        ->keyBy(fn ($day) => \Carbon\Carbon::parse($day->date)->toDateString());
                @endphp
                <div class="p-4 sm:p-6">
                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach($yearGuardias as $legendGuardia)
                            @php $legendColor = $yearGuardiaColors[$legendGuardia->id] ?? $yearGuardiaPalette[0]; @endphp
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-slate-700 bg-slate-950">
                                <span class="w-2.5 h-2.5 rounded-full {{ $legendColor['dot'] }}"></span>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-200">{{ $legendGuardia->name }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        @foreach(range(1, 12) as $monthNumber)
                            @php
                                $monthStart = now()->copy()->startOfYear()->month($monthNumber)->startOfMonth();
                                $monthEnd = $monthStart->copy()->endOfMonth();
                                $gridStart = $monthStart->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                                $gridEnd = $monthEnd->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                                $monthCursor = $gridStart->copy();
                            @endphp
                            <div class="rounded-2xl border border-slate-800 bg-slate-950 overflow-hidden">
                                <div class="px-4 py-3 border-b border-slate-800 bg-slate-900">
                                    @php
                                        $spanishMonths = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                                    @endphp
                                    <div class="text-sm sm:text-base font-bold uppercase tracking-wider text-slate-100">{{ $spanishMonths[$monthNumber - 1] }}</div>
                                </div>
                                <div class="grid grid-cols-7 bg-slate-900 border-b border-slate-800">
                                    @foreach(['D', 'L', 'M', 'M', 'J', 'V', 'S'] as $weekDay)
                                        <div class="px-1 py-2 text-center text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase border-r border-slate-800 last:border-r-0">{{ $weekDay }}</div>
                                    @endforeach
                                </div>
                                <div class="grid grid-cols-7 gap-px bg-slate-800">
                                    @while($monthCursor->lessThanOrEqualTo($gridEnd))
                                        @php
                                            $calendarKey = $monthCursor->toDateString();
                                            $calendarDay = $yearCalendarDays->get($calendarKey);
                                            $isCurrentMonthCell = $monthCursor->month === $monthNumber;
                                            $isTodayCell = $monthCursor->isToday();
                                        @endphp
                                        <div class="min-h-[74px] p-1.5 sm:p-2 {{ $isCurrentMonthCell ? 'bg-slate-950' : 'bg-slate-900/60' }} {{ $isTodayCell ? 'ring-1 ring-inset ring-emerald-400' : '' }}">
                                            <div class="flex items-start justify-between gap-1">
                                                <span class="text-[11px] sm:text-xs font-bold {{ $isCurrentMonthCell ? 'text-slate-100' : 'text-slate-500 dark:text-slate-400' }}">{{ $monthCursor->day }}</span>
                                                @if($isTodayCell)
                                                    <span class="text-[8px] px-1.5 py-0.5 rounded bg-emerald-500/15 text-emerald-300 font-bold uppercase">Hoy</span>
                                                @endif
                                            </div>
                                            @if($calendarDay && $calendarDay->guardia)
                                                @php $calendarColor = $yearGuardiaColors[$calendarDay->guardia->id] ?? $yearGuardiaPalette[0]; @endphp
                                                <div class="mt-2 px-1.5 py-1 rounded-lg border text-[10px] leading-tight font-bold break-words {{ $calendarColor['badge'] }}">{{ $calendarDay->guardia->name }}</div>
                                            @endif
                                        </div>
                                        @php $monthCursor->addDay(); @endphp
                                    @endwhile
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="academyModal" class="fixed inset-0 bg-slate-900 bg-opacity-75 hidden overflow-y-auto h-full w-full z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="relative p-6 border w-full max-w-lg shadow-2xl rounded-xl {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-900 border-slate-800' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700' }}">
            <div class="text-center mb-6">
                <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border border-slate-800' : 'bg-blue-100' }} mb-4">
                    <i class="fas fa-chalkboard-user {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-blue-300' : 'text-blue-600' }} text-2xl"></i>
                </div>
                <h3 class="text-2xl font-bold {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-100' : 'text-slate-800 dark:text-white' }}">Registrar Academia</h3>
                <p class="{{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-400' : 'text-slate-500 dark:text-slate-400' }} text-sm mt-1">Academia nocturna</p>
            </div>

            <form action="{{ route('novelties.store_web') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="Academia">

                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700 dark:text-slate-300' }}">A cargo</label>
                    <select name="firefighter_id" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border-slate-800 text-slate-100' : 'bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 text-slate-900' }}" required>
                        <option value="" disabled selected>Seleccionar</option>
                        @foreach(($academyLeadersFirefighters ?? collect()) as $leader)
                            <option value="{{ $leader->id }}">{{ $leader->apellido_paterno }} {{ $leader->apellido_materno ? $leader->apellido_materno . ',' : ',' }} {{ $leader->nombres }}</option>
                        @endforeach
                    </select>
                    @if(!isset($academyLeadersFirefighters) || ($academyLeadersFirefighters ?? collect())->isEmpty())
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">No se detectó personal en la guardia nocturna.</div>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700 dark:text-slate-300' }}">Día y hora</label>
                    <input type="datetime-local" name="date" value="{{ now()->copy()->setTimezone($guardiaTz)->format('Y-m-d\\TH:i') }}" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border-slate-800 text-slate-100' : 'bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 text-slate-900' }}" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700 dark:text-slate-300' }}">Título</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border-slate-800 text-slate-100 placeholder:text-slate-500 dark:text-slate-400' : 'bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 text-slate-900' }}" placeholder="Ej: RCP, Uso de ERA, etc" required>
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold mb-2 uppercase tracking-wide {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-300' : 'text-slate-700 dark:text-slate-300' }}">Descripción</label>
                    <textarea name="description" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-h-[100px] {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 border-slate-800 text-slate-100 placeholder:text-slate-500 dark:text-slate-400' : 'bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 text-slate-900' }}" placeholder="Detalle de la academia..." required>{{ old('description') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeAcademyModal()" class="w-1/2 px-4 py-2.5 font-bold rounded-lg transition-colors uppercase text-sm {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 hover:bg-slate-900 text-slate-100 border border-slate-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200' }}">
                        Cancelar
                    </button>
                    <button type="submit" class="w-1/2 px-4 py-2.5 bg-blue-700 text-white font-bold rounded-lg hover:bg-blue-800 transition-colors shadow-md uppercase text-sm">
                        Registrar
                    </button>
                </div>

            </form>
        </div>
    </div>

    @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
        <div id="refuerzoModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-50 flex items-center justify-center opacity-0 transition-opacity duration-300">
            <div class="bg-slate-900 text-slate-100 rounded-xl shadow-2xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 p-6 border border-slate-800">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-100 uppercase tracking-tight">Agregar Refuerzo</h3>
                        <p class="text-sm text-slate-400 mt-1">El refuerzo se libera automáticamente a las 10:00 AM del día siguiente.</p>
                    </div>
                    <button type="button" onclick="closeRefuerzoModal()" class="text-slate-400 hover:text-slate-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.guardias.refuerzo') }}">
                    @csrf
                    <input type="hidden" name="guardia_id" value="{{ $myGuardia?->id }}">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Voluntario</label>
                            <!-- Custom Professional Dropdown -->
                            <div class="relative" id="refuerzo-select-container">
                                <input type="hidden" name="firefighter_id" id="refuerzo_firefighter_id" required>
                                
                                <!-- Search Input -->
                                <div class="relative">
                                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" id="refuerzo-search-input"
                                        class="w-full text-sm border-slate-800 rounded-lg shadow-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 pl-9 pr-10 py-2.5 bg-slate-950 text-slate-100 placeholder:text-slate-500 dark:text-slate-400 cursor-pointer"
                                        placeholder="Buscar voluntario..." autocomplete="off" readonly>
                                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                                
                                <!-- Dropdown Menu -->
                                <div id="refuerzo-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-slate-900 border border-slate-700 rounded-lg shadow-xl z-50 max-h-64 overflow-y-auto">
                                    <div class="p-2 sticky top-0 bg-slate-900 border-b border-slate-800">
                                        <input type="text" id="refuerzo-filter-input" 
                                            class="w-full text-xs bg-slate-800 border-slate-700 rounded px-2 py-1.5 text-slate-200 placeholder:text-slate-500 dark:text-slate-400 focus:outline-none focus:border-sky-500"
                                            placeholder="Filtrar por nombre o RUT...">
                                    </div>
                                    <div id="refuerzo-options-list" class="py-1">
                                        @foreach($replacementCandidates as $cand)
                                            <div class="refuerzo-option px-3 py-2 hover:bg-slate-800 cursor-pointer transition-colors flex items-center gap-3"
                                                 data-value="{{ $cand->id }}"
                                                 data-search="{{ strtolower(trim($cand->nombres . ' ' . $cand->apellido_paterno . ' ' . ($cand->apellido_materno ?? '') . ' ' . ($cand->rut ?? ''))) }}">
                                                <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 text-xs font-bold">
                                                    {{ strtoupper(substr($cand->nombres, 0, 1)) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-medium text-slate-200 truncate">
                                                        {{ trim($cand->nombres . ' ' . $cand->apellido_paterno) }}
                                                    </div>
                                                    @if($cand->rut)
                                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $cand->rut }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div id="refuerzo-no-results" class="hidden px-3 py-4 text-center text-xs text-slate-500 dark:text-slate-400">
                                        No se encontraron resultados
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" onclick="closeRefuerzoModal()" class="w-1/2 py-2.5 px-4 rounded-lg border border-slate-800 bg-slate-950 text-slate-100 font-bold text-sm hover:bg-slate-900 transition-colors uppercase">
                                Cancelar
                            </button>
                            <button type="submit" class="w-1/2 py-2.5 px-4 rounded-lg bg-sky-600 text-white font-bold text-sm hover:bg-sky-700 shadow-md hover:shadow-lg transition-all uppercase flex items-center justify-center gap-2">
                                <span>Confirmar</span>
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="hidden border-2 border-rose-400 border-emerald-400 border-slate-800"></div>

    <div id="confirm-error-toast" class="fixed inset-0 z-[60] hidden items-center justify-center">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeConfirmErrorToast()"></div>
        <div class="relative bg-slate-900 border border-rose-500/50 rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-rose-500/20 flex items-center justify-center">
                <i class="fas fa-circle-xmark text-rose-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-white uppercase tracking-wider mb-2">Código Incorrecto</h3>
            <p id="confirm-error-text" class="text-sm text-slate-400 mb-4">El código ingresado no es válido.</p>
            <button onclick="closeConfirmErrorToast()" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold uppercase tracking-wider text-xs py-3 rounded-xl border border-rose-700">
                Aceptar
            </button>
        </div>
    </div>

    <script>
        window.closeConfirmErrorToast = function() {
            const toast = document.getElementById('confirm-error-toast');
            if (toast) {
                toast.classList.add('hidden');
                toast.classList.remove('flex');
            }
        }

        window.closeAttendanceStaleBanner = function() {
            const banner = document.getElementById('attendance-stale-banner');
            if (banner) {
                banner.classList.add('hidden');
                banner.classList.remove('flex');
            }
        }
        window.__guardiaId = @json((int) ($myGuardia->id ?? 0));
        window.__attendanceSavedToday = @json((bool) ($hasAttendanceSavedToday ?? false));
        window.__attendanceDirty = false;
        window.__attendanceSubmitting = false;
        window.__attendanceIsStale = @json((bool) ($attendanceIsStale ?? false));

        function getLocalYmd() {
            try {
                return new Date().toLocaleDateString('sv-SE');
            } catch (e) {
                return new Date().toISOString().slice(0, 10);
            }
        }

        window.__guardiaSnapshot = {
            latest_novelty_at: @json(optional(($guardiaNovelties ?? null)?->first()?->updated_at ?? null)?->toISOString()),
            latest_bombero_at: @json(optional(($myStaff ?? collect())->max('updated_at') ?? null)?->toISOString()),
            latest_replacement_at: @json(optional(($replacementByOriginal ?? collect())->max('updated_at') ?? null)?->toISOString()),
            latest_bed_assignment_at: @json(optional(\App\Models\BedAssignment::query()->latest('updated_at')->value('updated_at'))?->toISOString()),
            attendance_saved_at: @json(optional(($hasAttendanceSavedToday ?? false) ? (\App\Models\GuardiaAttendanceRecord::where('guardia_id', $myGuardia->id ?? null)->whereDate('date', \Carbon\Carbon::today()->toDateString())->value('saved_at')) : null)?->toISOString()),
            latest_draft_at: @json(optional($latestDraftAt ?? null)?->toISOString()),
        };

        const __attendanceEnableTime  = @json(\App\Models\SystemSetting::getValue('attendance_enable_time', '22:00'));
        const __attendanceDisableTime = @json(\App\Models\SystemSetting::getValue('attendance_disable_time', '07:00'));

        function isAttendanceWindowOpen() {
            const now = new Date();
            const nowMins = now.getHours() * 60 + now.getMinutes();
            const [eH, eM] = __attendanceEnableTime.split(':').map(Number);
            const [dH, dM] = __attendanceDisableTime.split(':').map(Number);
            const enableMins  = eH * 60 + eM;
            const disableMins = dH * 60 + dM;
            if (enableMins > disableMins) {
                return nowMins >= enableMins || nowMins < disableMins;
            }
            return nowMins >= enableMins && nowMins < disableMins;
        }

        function markAttendanceDirty() {
            if (!window.__attendanceSavedToday) return;
            if (window.__attendanceDirty) return;
            window.__attendanceDirty = true;

            const banner = document.getElementById('attendance-stale-banner');
            const badge = document.getElementById('attendance-saved-badge');

            // Si está fuera del horario de registro (07:00-22:00), mostrar mensaje diferente
            if (!isAttendanceWindowOpen()) {
                if (badge) {
                    badge.classList.remove('border-emerald-200', 'bg-emerald-50', 'text-emerald-700', 'border-amber-200', 'bg-amber-50', 'text-amber-800');
                    badge.classList.add('border-slate-300 dark:border-slate-600', 'bg-slate-100 dark:bg-slate-800', 'text-slate-500 dark:text-slate-400');
                    badge.textContent = 'FUERA DE HORARIO DE REGISTRO';
                }
                // No mostrar el banner modal fuera de horario
                if (banner) {
                    banner.classList.add('hidden');
                    banner.classList.remove('flex');
                }
            } else {
                // Dentro del horario permitido (22:00-07:00): mostrar banner normal
                if (banner) {
                    banner.classList.remove('hidden');
                    banner.classList.add('flex');
                }
                if (badge) {
                    badge.classList.remove('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
                    badge.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-800');
                    badge.textContent = 'ASISTENCIA DESACTUALIZADA';
                }
            }

            // Re-evaluar el botón de guardar ahora que __attendanceDirty es true
            refreshAttendanceSubmitButton();
        }

        window.addEventListener('beforeunload', function (e) {
            if (window.__attendanceSubmitting) return;
            if (!window.__attendanceDirty) return;
            e.preventDefault();
            e.returnValue = '';
        });

        // Suppress beforeunload for any non-attendance form navigation
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && form.id !== 'guardia-attendance-form') {
                window.__attendanceSubmitting = true;
            }
        }, true);

        window.bindAttendanceFormHandlers = function() {
            const form = document.getElementById('guardia-attendance-form');
            const submitBtn = document.getElementById('guardia-attendance-submit');
            if (!form || form.getAttribute('data-bound') === '1') return;
            form.setAttribute('data-bound', '1');

            const markSubmitting = function() {
                window.__attendanceSubmitting = true;
                window.__attendanceDirty = false;
                window.__attendanceIsStale = false;

                // Defer UI mutations to avoid cancelling the native submit in some browsers.
                setTimeout(function() {
                    try {
                        const banner = document.getElementById('attendance-stale-banner');
                        if (banner) {
                            banner.classList.add('hidden');
                            banner.classList.remove('flex');
                        }
                    } catch (e) {}

                    try {
                        const badge = document.getElementById('attendance-saved-badge');
                        if (badge) {
                            badge.textContent = 'GUARDANDO ASISTENCIA...';
                            badge.classList.remove('border-red-200', 'bg-red-50', 'text-red-700');
                            badge.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-800');
                        }
                    } catch (e) {}

                    if (submitBtn) {
                        submitBtn.setAttribute('disabled', 'disabled');
                        submitBtn.classList.remove('bg-slate-800','hover:bg-slate-700','text-slate-100','border-slate-700');
                        submitBtn.classList.add('bg-slate-200','text-slate-500 dark:text-slate-400','border-slate-300 dark:border-slate-600','cursor-not-allowed');
                    }
                }, 0);
            };

            form.addEventListener('submit', function() {
                markSubmitting();
            });
        };

        window.openNoveltyModal = function() {
            const modal = document.getElementById('noveltyModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        window.closeNoveltyModal = function() {
            const modal = document.getElementById('noveltyModal');
            if (modal) modal.classList.add('hidden');
        }

        window.openAcademyModal = function() {
            const modal = document.getElementById('academyModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        window.closeAcademyModal = function() {
            const modal = document.getElementById('academyModal');
            if (modal) modal.classList.add('hidden');
        }

        window.openCalendarPopup = function() {
            const modal = document.getElementById('yearCalendarModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        window.closeCalendarPopup = function() {
            const modal = document.getElementById('yearCalendarModal');
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        window.openRefuerzoModal = function() {
            const modal = document.getElementById('refuerzoModal');
            if (!modal) return;
            
            const content = modal.firstElementChild;
            modal.classList.remove('hidden');
            
            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0');
                if (content) {
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }
            });
        }

        window.closeRefuerzoModal = function() {
            const modal = document.getElementById('refuerzoModal');
            if (!modal) return;

            const content = modal.firstElementChild;
            modal.classList.add('opacity-0');
            if (content) {
                content.classList.remove('scale-100');
                content.classList.add('scale-95');
            }

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function syncDatalistHiddenId(displayInput, datalistEl, hiddenInput) {
            if (!displayInput || !datalistEl || !hiddenInput) return;

            const displayValue = (displayInput.value || '').trim();
            hiddenInput.value = '';

            if (!displayValue) return;

            const options = datalistEl.options;
            for (let i = 0; i < options.length; i++) {
                if ((options[i].value || '').trim() === displayValue) {
                    hiddenInput.value = options[i].getAttribute('data-value') || '';
                    break;
                }
            }
        }

        window.removeRefuerzo = function(guardiaId, firefighterId) {
            if (!confirm('¿Quitar este refuerzo y devolverlo a su guardia anterior?')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @json(route('admin.guardias.refuerzo.remove'));

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = @json(csrf_token());
            form.appendChild(csrf);

            const guardiaInput = document.createElement('input');
            guardiaInput.type = 'hidden';
            guardiaInput.name = 'guardia_id';
            guardiaInput.value = guardiaId;
            form.appendChild(guardiaInput);

            const firefighterInput = document.createElement('input');
            firefighterInput.type = 'hidden';
            firefighterInput.name = 'firefighter_id';
            firefighterInput.value = firefighterId;
            form.appendChild(firefighterInput);

            document.body.appendChild(form);
            form.submit();
        }

        window.openReplacementModal = function(originalUserId, originalUserName) {
            const modal = document.getElementById('replacementModal');
            const guardiaIdInput = document.getElementById('modal_guardia_id');
            const originalIdInput = document.getElementById('modal_original_firefighter_id');
            const nameEl = document.getElementById('modal_original_user_name');
            const displayInput = document.querySelector('#replacementModal input[list="modal_volunteers_list"]');
            const replacementIdInput = document.getElementById('modal_replacement_firefighter_id');

            if (!modal || !guardiaIdInput || !originalIdInput || !nameEl || !replacementIdInput) {
                console.error('Modal de reemplazo no encontrado o incompleto', {
                    hasModal: !!modal,
                    hasGuardiaIdInput: !!guardiaIdInput,
                    hasOriginalIdInput: !!originalIdInput,
                    hasNameEl: !!nameEl,
                    hasReplacementIdInput: !!replacementIdInput,
                });
                return;
            }

            guardiaIdInput.value = {!! json_encode($myGuardia?->id) !!};
            originalIdInput.value = originalUserId;
            nameEl.textContent = originalUserName;

            if (displayInput) displayInput.value = '';
            replacementIdInput.value = '';

            const content = modal.firstElementChild;
            modal.classList.remove('hidden');

            requestAnimationFrame(() => {
                modal.classList.remove('opacity-0');
                if (content) {
                    content.classList.remove('scale-95');
                    content.classList.add('scale-100');
                }
            });

            const card = document.getElementById('guardia-card-' + userId);
            if (card) {
                const lockEl = card.querySelector('[data-lock-attendance="1"]');
                const isRefuerzo = card.textContent.includes('REFUERZO');
                const requires = (status === 'constituye' || status === 'reemplazo' || isRefuerzo);
                card.setAttribute('data-requires-confirmation', requires ? '1' : '0');
                if (!requires) {
                    card.classList.remove('ring-2','ring-rose-400','ring-emerald-400');
                    card.setAttribute('data-is-confirmed', '0');
                }
            }

            refreshAttendanceSubmitButton();
        }

        document.addEventListener('DOMContentLoaded', function() {
            window.__draftEditable = false;

            const csrf = () => (document.querySelector('meta[name="csrf-token"]')?.content || '');

            const resetAllConfirmations = function() {
                document.querySelectorAll('[data-card-user]').forEach(card => {
                    const userId = card.getAttribute('data-card-user');
                    if (!userId) return;
                    const tokenEl = document.getElementById('confirm-token-' + userId);
                    if (tokenEl) tokenEl.value = '';
                    setConfirmState(userId, false);
                });
                refreshAttendanceSubmitButton();
            };

            const loadDraftState = async function() {
                try {
                    const res = await fetch('{{ route('draft.turno.current') }}', {
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });

                    if (!res.ok) return;
                    const data = await res.json().catch(() => null);
                    if (!data || !data.ok) return;

                    window.__draftEditable = !!data.editable;

                    const items = Array.isArray(data.items) ? data.items : [];

                    // Si el draft viene vacío, crear items base para todas las tarjetas visibles
                    if (items.length === 0 && window.__draftEditable) {
                        try {
                            const payloadItems = [];
                            document.querySelectorAll('[data-card-user]').forEach(card => {
                                const userId = card.getAttribute('data-card-user');
                                if (!userId) return;
                                const input = document.getElementById('attendance-status-' + userId);
                                const status = (input?.value || 'constituye').toLowerCase();
                                payloadItems.push({
                                    firefighter_id: parseInt(userId, 10),
                                    attendance_status: status,
                                });
                            });

                            if (payloadItems.length > 0) {
                                await fetch('{{ route('draft.turno.seed') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrf(),
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({ items: payloadItems }),
                                });
                            }
                        } catch (e) {}

                        // Re-cargar estado ya con items creados
                        await loadDraftState();
                        return;
                    }

                    // Limpiar estado solo de tarjetas que ya NO están en el draft
                    const draftUserIds = new Set(items.map(it => String(it.firefighter_id)));
                    document.querySelectorAll('[data-card-user]').forEach(card => {
                        const uid = card.getAttribute('data-card-user');
                        if (uid && !draftUserIds.has(uid)) {
                            setConfirmState(uid, false);
                        }
                    });

                    items.forEach(it => {
                        const userId = it.firefighter_id;
                        if (!userId) return;

                        // 1) Rehidratar estado (para colores/label) si viene desde el draft
                        if (it.attendance_status) {
                            const input = document.getElementById('attendance-status-' + userId);
                            if (input) {
                                input.value = String(it.attendance_status).toLowerCase();
                            }
                            updateGuardiaCardUI(userId, String(it.attendance_status).toLowerCase());
                        }

                        const tokenEl = document.getElementById('confirm-token-' + userId);
                        if (tokenEl) tokenEl.value = it.confirm_token || '';

                        // 2) Recalcular si requiere confirmación (constituye/reemplazo/refuerzo)
                        const card = document.getElementById('guardia-card-' + userId);
                        if (card) {
                            const input = document.getElementById('attendance-status-' + userId);
                            const status = (input?.value || 'constituye').toLowerCase();
                            const isRefuerzo = card.textContent.includes('REFUERZO');
                            const requires = (status === 'constituye' || status === 'reemplazo' || isRefuerzo);
                            card.setAttribute('data-requires-confirmation', requires ? '1' : '0');
                        }

                        // 3) Rehidratar confirmación solo si la tarjeta la requiere
                        const requiresConfirmation = (function() {
                            const card = document.getElementById('guardia-card-' + userId);
                            return card ? (card.getAttribute('data-requires-confirmation') === '1') : true;
                        })();

                        const confirmed = requiresConfirmation && !!(it.confirm_token && it.confirmed_at);
                        setConfirmState(userId, confirmed);
                    });

                    // Sync: detectar tarjetas visibles que no están en el draft (ej: refuerzo nuevo)
                    if (window.__draftEditable && items.length > 0) {
                        const draftIds = new Set(items.map(it => String(it.firefighter_id)));
                        const missingItems = [];
                        document.querySelectorAll('[data-card-user]').forEach(card => {
                            const uid = card.getAttribute('data-card-user');
                            if (!uid || draftIds.has(uid)) return;
                            const inp = document.getElementById('attendance-status-' + uid);
                            const st = (inp?.value || 'constituye').toLowerCase();
                            missingItems.push({ firefighter_id: parseInt(uid, 10), attendance_status: st });
                        });

                        if (missingItems.length > 0) {
                            // Nuevo refuerzo/reemplazo agregado después de guardar → habilitar botón
                            // Solo marcar dirty si estamos dentro de la ventana de asistencia
                            if (window.__attendanceSavedToday && isAttendanceWindowOpen()) {
                                markAttendanceDirty();
                            }
                            try {
                                await fetch('{{ route('draft.turno.seed') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrf(),
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({ items: missingItems }),
                                });
                            } catch (e) {}
                        }
                    }

                    refreshAttendanceSubmitButton();

                } catch (e) {
                }
            };

            window.__loadDraftState = loadDraftState;
            window.__resetAllConfirmations = resetAllConfirmations;

            window.__persistDraftConfirmation = async function(userId, token) {
                if (!window.__draftEditable) return;

                try {
                    await fetch('{{ route('draft.turno.confirm') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            firefighter_id: parseInt(userId, 10),
                            confirm_token: token,
                        }),
                    });
                } catch (e) {
                }
            };

            window.__persistDraftItemStatus = async function(userId, status) {
                if (!window.__draftEditable) return;

                try {
                    await fetch('{{ route('draft.turno.item') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf(),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            firefighter_id: parseInt(userId, 10),
                            attendance_status: String(status || 'constituye').toLowerCase(),
                        }),
                    });
                } catch (e) {
                }
            };

            loadDraftState();

            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                    return;
                }
                loadDraftState();
            });

            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    setTimeout(function() {
                        loadDraftState();
                    }, 100);
                }
            });
        });

        window.toggleInhabilitado = function(firefighterId) {
            if (!confirm('¿Inhabilitar este bombero?')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url('/') }}/admin/bomberos/' + firefighterId + '/toggle-fuera-servicio';

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            document.body.appendChild(form);
            form.submit();
        }

        window.toggleHabilitar = function(firefighterId) {
            if (!confirm('¿Volver a habilitar este bombero?')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ url('/') }}/admin/bomberos/' + firefighterId + '/toggle-fuera-servicio';

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            document.body.appendChild(form);
            form.submit();
        }

        window.closeReplacementModal = function() {
            const modal = document.getElementById('replacementModal');
            if (!modal) return;

            const content = modal.firstElementChild;
            modal.classList.add('opacity-0');
            if (content) {
                content.classList.remove('scale-100');
                content.classList.add('scale-95');
            }

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        window.updateModalReplacementUserId = function(input) {
            const list = document.getElementById('modal_volunteers_list');
            const hiddenInput = document.getElementById('modal_replacement_firefighter_id');
            if (!list || !hiddenInput) return;

            syncDatalistHiddenId(input, list, hiddenInput);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const refuerzoDisplay = document.querySelector('input[list="refuerzo_volunteers_list"]');
            const refuerzoList = document.getElementById('refuerzo_volunteers_list');
            const refuerzoHidden = document.getElementById('refuerzo_firefighter_id');

            const replDisplay = document.querySelector('#replacementModal input[list="modal_volunteers_list"]');
            const replList = document.getElementById('modal_volunteers_list');
            const replHidden = document.getElementById('modal_replacement_firefighter_id');
            if (replDisplay && replList && replHidden) {
                ['change', 'blur'].forEach(evt => {
                    replDisplay.addEventListener(evt, () => syncDatalistHiddenId(replDisplay, replList, replHidden));
                });

                const replForm = replDisplay.closest('form');
                if (replForm) {
                    replForm.addEventListener('submit', (e) => {
                        syncDatalistHiddenId(replDisplay, replList, replHidden);
                        if ((replHidden.value || '').trim() === '') {
                            e.preventDefault();
                            alert('Debes seleccionar un voluntario de la lista.');
                        }
                    });
                }
            }
        });

        document.addEventListener('click', function(event) {
            const btn = event.target.closest('[data-open-replacement="1"]');
            if (!btn) return;

            const originalUserId = btn.getAttribute('data-original-firefighter-id');
            const originalUserName = btn.getAttribute('data-original-user-name') || '';
            window.openReplacementModal(originalUserId, originalUserName);
        });
        
        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                window.closeNoveltyModal();
                window.closeReplacementModal();
                window.closeAcademyModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Si hay errores de validación, reabrir el modal automáticamente
            @if($errors->has('title') || $errors->has('type') || $errors->has('description'))
                openNoveltyModal();
            @endif

            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('es-CL', { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit',
                    hour12: false
                });
                
                const clockElement = document.getElementById('digital-clock');
                if(clockElement) {
                    clockElement.textContent = timeString;
                }
            }
            
            updateClock();
            setInterval(updateClock, 1000);
        });

        window.setGuardiaStatus = function(userId, status) {
            if (!window.__draftEditable) {
                const toast = document.getElementById('confirm-error-toast');
                const toastText = document.getElementById('confirm-error-text');
                if (toast && toastText) {
                    toastText.textContent = 'EDICIÓN BLOQUEADA FUERA DEL HORARIO 22:00 - 07:00.';
                    toast.classList.remove('hidden');
                    toast.classList.add('flex');
                }
                return;
            }

            if (status === 'falta') {
                if (!confirm('¿Cambiar estado a FALTA?')) {
                    return;
                }
            }

            const input = document.getElementById('attendance-status-' + userId);
            if (!input) {
                console.error('Input de estado no encontrado', { userId });
                return;
            }

            input.value = status;
            clearConfirmation(userId);
            updateGuardiaCardUI(userId, status);
            markAttendanceDirty();

            try {
                if (typeof window.__persistDraftItemStatus === 'function') {
                    window.__persistDraftItemStatus(userId, status);
                }
            } catch (e) {}
        }

        window.cycleGuardiaStatus = function(userId) {
            const input = document.getElementById('attendance-status-' + userId);
            if (!input) return;
            const current = (input.value || 'constituye').toLowerCase();

            const order = ['constituye', 'permiso', 'ausente', 'licencia', 'falta'];
            const idx = order.indexOf(current);
            const next = order[(idx === -1 ? 0 : (idx + 1) % order.length)];

            window.setGuardiaStatus(userId, next);
        }

        function refreshAttendanceSubmitButton() {
            const submitBtn = document.getElementById('guardia-attendance-submit');
            if (!submitBtn) return;

            // Fuera del horario permitido (07:00-22:00): siempre deshabilitado
            if (!isAttendanceWindowOpen()) {
                submitBtn.setAttribute('disabled', 'disabled');
                submitBtn.classList.remove('bg-slate-800','hover:bg-slate-700','text-slate-100','border-slate-700');
                submitBtn.classList.add('bg-slate-200','text-slate-500 dark:text-slate-400','border-slate-300 dark:border-slate-600','cursor-not-allowed');
                return;
            }

            const cards = document.querySelectorAll('[data-card-user][data-requires-confirmation="1"]');
            let hasUnconfirmed = false;
            cards.forEach(card => {
                if (card.getAttribute('data-is-confirmed') !== '1') hasUnconfirmed = true;
            });

            // Si ya se guardó y no hay cambios nuevos NI bomberos sin confirmar, bloquear el botón
            if (window.__attendanceSavedToday && !window.__attendanceDirty && !hasUnconfirmed) {
                submitBtn.setAttribute('disabled', 'disabled');
                submitBtn.classList.remove('bg-slate-800','hover:bg-slate-700','text-slate-100','border-slate-700');
                submitBtn.classList.add('bg-slate-200','text-slate-500 dark:text-slate-400','border-slate-300 dark:border-slate-600','cursor-not-allowed');
                return;
            }

            // Si todos los que requieren confirmación están confirmados, habilitar
            if (!hasUnconfirmed) {
                submitBtn.removeAttribute('disabled');
                submitBtn.classList.remove('bg-slate-200','text-slate-500 dark:text-slate-400','border-slate-300 dark:border-slate-600','cursor-not-allowed');
                submitBtn.classList.add('bg-slate-800','hover:bg-slate-700','text-slate-100','border-slate-700');
            } else {
                submitBtn.setAttribute('disabled', 'disabled');
                submitBtn.classList.remove('bg-slate-800','hover:bg-slate-700','text-slate-100','border-slate-700');
                submitBtn.classList.add('bg-slate-200','text-slate-500 dark:text-slate-400','border-slate-300 dark:border-slate-600','cursor-not-allowed');
            }
        }

        function setConfirmState(userId, confirmed) {
            const card = document.getElementById('guardia-card-' + userId);
            if (!card) return;

            // Guard: si el estado ya es el correcto, no tocar el DOM (evita pestañeo)
            const currentState = card.getAttribute('data-is-confirmed') === '1';
            if (currentState === confirmed) return;

            card.setAttribute('data-is-confirmed', confirmed ? '1' : '0');
            card.style.borderWidth = '';
            card.style.borderColor = '';
            if (confirmed) {
                card.style.borderWidth = '2px';
                card.style.borderColor = '#34d399';
            }

            const controls = document.getElementById('confirm-controls-' + userId);
            if (controls) {
                controls.style.display = confirmed ? 'none' : '';
            }

            const msgEl = document.getElementById('confirm-msg-' + userId);
            if (msgEl) {
                if (confirmed) {
                    msgEl.textContent = 'CONFIRMADO';
                    msgEl.classList.remove('text-rose-200','text-slate-400');
                    msgEl.classList.add('text-emerald-200');
                } else {
                    msgEl.textContent = '';
                    msgEl.classList.remove('text-emerald-200','text-rose-200');
                    msgEl.classList.add('text-slate-400');
                }
            }

            const statusEl = document.getElementById('confirm-status-' + userId);
            if (statusEl) {
                if (confirmed) {
                    statusEl.innerHTML = '<span class="flex items-center gap-1"><i class="fas fa-check-circle text-emerald-400"></i></span>';
                    statusEl.classList.remove('text-rose-200');
                    statusEl.classList.add('text-emerald-200');
                } else {
                    statusEl.textContent = 'NO CONFIRMADO';
                    statusEl.classList.remove('text-emerald-200');
                    statusEl.classList.add('text-rose-200');
                }
            }

            // Update status button to show confirmation state
            const cycleBtn = document.getElementById('status-cycle-' + userId);
            const cycleLbl = document.getElementById('status-cycle-label-' + userId);
            if (cycleBtn && cycleLbl) {
                const currentLabel = cycleLbl.textContent;
                if (confirmed) {
                    // Add checkmark to button when confirmed
                    if (!currentLabel.includes('✓')) {
                        cycleLbl.innerHTML = currentLabel + ' <i class="fas fa-check ml-1"></i>';
                    }
                    cycleBtn.classList.remove('opacity-60');
                    cycleBtn.classList.add('ring-2', 'ring-emerald-400', 'ring-offset-2', 'ring-offset-slate-900');
                } else {
                    // Remove checkmark when not confirmed
                    cycleLbl.textContent = currentLabel.replace(' ✓', '').replace(' <i class="fas fa-check ml-1"></i>', '');
                    cycleBtn.classList.remove('ring-2', 'ring-emerald-400', 'ring-offset-2', 'ring-offset-slate-900');
                }
            }
        }

        function clearConfirmation(userId) {
            const tokenEl = document.getElementById('confirm-token-' + userId);
            if (tokenEl) tokenEl.value = '';

            try {
                const input = document.getElementById('attendance-status-' + userId);
                const current = (input?.value || 'constituye').toLowerCase();
                if (typeof window.__persistDraftItemStatus === 'function') {
                    window.__persistDraftItemStatus(userId, current);
                }
            } catch (e) {}

            const msgEl = document.getElementById('confirm-msg-' + userId);
            if (msgEl) {
                msgEl.textContent = '';
                msgEl.classList.remove('text-emerald-200','text-rose-200');
                msgEl.classList.add('text-slate-400');
            }
            setConfirmState(userId, false);
            refreshAttendanceSubmitButton();
        }

        window.confirmBombero = async function(guardiaId, bomberoId) {
            const codeEl = document.getElementById('confirm-code-' + bomberoId);
            const tokenEl = document.getElementById('confirm-token-' + bomberoId);
            const btnEl = document.getElementById('confirm-btn-' + bomberoId);

            const numeroRegistro = (codeEl?.value || '').trim();
            if (!numeroRegistro) {
                const toast = document.getElementById('confirm-error-toast');
                const toastText = document.getElementById('confirm-error-text');
                if (toast && toastText) {
                    toastText.textContent = 'INGRESA EL CÓDIGO DEL BOMBERO';
                    toast.classList.remove('hidden');
                    toast.classList.add('flex');
                }
                return;
            }

            if (!window.__draftEditable) {
                const toast = document.getElementById('confirm-error-toast');
                const toastText = document.getElementById('confirm-error-text');
                if (toast && toastText) {
                    toastText.textContent = 'EDICIÓN BLOQUEADA FUERA DEL HORARIO 22:00 - 07:00.';
                    toast.classList.remove('hidden');
                    toast.classList.add('flex');
                }
                return;
            }

            try {
                if (btnEl) {
                    btnEl.setAttribute('disabled', 'disabled');
                    btnEl.classList.add('opacity-60','cursor-not-allowed');
                }

                const res = await fetch(`/admin/guardias/${guardiaId}/bomberos/${bomberoId}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ numero_registro: numeroRegistro }),
                });

                if (res.status === 401) {
                    window.location.reload();
                    return;
                }

                let data = null;
                const raw = await res.text().catch(() => '');
                try {
                    data = raw ? JSON.parse(raw) : null;
                } catch (e) {
                    data = null;
                }

                if (!res.ok || !data || !data.ok) {
                    const errMsg = (data && (data.message || data.error)) ? (data.message || data.error) : 'NO SE PUDO CONFIRMAR';
                    const toast = document.getElementById('confirm-error-toast');
                    const toastText = document.getElementById('confirm-error-text');
                    if (toast && toastText) {
                        toastText.textContent = String(errMsg).toUpperCase();
                        toast.classList.remove('hidden');
                        toast.classList.add('flex');
                    }
                    clearConfirmation(bomberoId);
                    return;
                }

                if (tokenEl) tokenEl.value = data.token || '';
                setConfirmState(bomberoId, true);

                try {
                    if (data.token && typeof window.__persistDraftConfirmation === 'function') {
                        await window.__persistDraftConfirmation(bomberoId, data.token);
                    }
                } catch (e) {}

                refreshAttendanceSubmitButton();
            } catch (e) {
                const toast = document.getElementById('confirm-error-toast');
                const toastText = document.getElementById('confirm-error-text');
                if (toast && toastText) {
                    toastText.textContent = 'ERROR AL CONFIRMAR';
                    toast.classList.remove('hidden');
                    toast.classList.add('flex');
                }
                clearConfirmation(bomberoId);
            } finally {
                if (btnEl) {
                    btnEl.removeAttribute('disabled');
                    btnEl.classList.remove('opacity-60','cursor-not-allowed');
                }
            }
        }

        function updateGuardiaCardUI(userId, status) {
            // Guard: si el status no cambió, no tocar el DOM
            const input = document.getElementById('attendance-status-' + userId);
            if (input && input.getAttribute('data-last-ui-status') === status) return;
            if (input) input.setAttribute('data-last-ui-status', status);

            const header = document.getElementById('card-header-' + userId);
            if (header) {
                header.classList.remove('bg-emerald-950/40','bg-purple-950/40','bg-amber-950/35','bg-slate-950','bg-blue-950/40','bg-rose-950/40');
                if (status === 'constituye') header.classList.add('bg-emerald-950/40');
                else if (status === 'reemplazo') header.classList.add('bg-purple-950/40');
                else if (status === 'permiso') header.classList.add('bg-amber-950/35');
                else if (status === 'ausente') header.classList.add('bg-slate-950');
                else if (status === 'licencia') header.classList.add('bg-blue-950/40');
                else if (status === 'falta') header.classList.add('bg-rose-950/40');
                else header.classList.add('bg-slate-950');
            }

            const card = document.getElementById('guardia-card-' + userId);
            const lockEl = card ? card.querySelector('[data-lock-attendance="1"]') : null;

            const cycleBtn = document.getElementById('status-cycle-' + userId);
            const cycleLbl = document.getElementById('status-cycle-label-' + userId);
            if (cycleBtn && cycleLbl) {
                const labelMap = {
                    constituye: 'CONSTITUYE',
                    permiso: 'PERMISO',
                    ausente: 'AUSENTE',
                    licencia: 'LICENCIA',
                    falta: 'FALTA',
                };
                const themeRemove = [
                    'bg-emerald-500/80','border-emerald-400/50',
                    'bg-amber-500/80','border-amber-400/50',
                    'bg-slate-400/30','border-slate-500/30',
                    'bg-blue-600/80','border-blue-400/50',
                    'bg-red-600/80','border-red-400/50'
                ];
                cycleBtn.classList.remove(...themeRemove);

                const s = (status || 'constituye').toLowerCase();
                cycleLbl.textContent = labelMap[s] || 'CONSTITUYE';
                if (s === 'constituye') cycleBtn.classList.add('bg-emerald-500/80','border-emerald-400/50');
                else if (s === 'permiso') cycleBtn.classList.add('bg-amber-500/80','border-amber-400/50');
                else if (s === 'ausente') cycleBtn.classList.add('bg-slate-400/30','border-slate-500/30');
                else if (s === 'licencia') cycleBtn.classList.add('bg-blue-600/80','border-blue-400/50');
                else if (s === 'falta') cycleBtn.classList.add('bg-red-600/80','border-red-400/50');

                if (lockEl) {
                    lockEl.style.display = (status === 'constituye' || status === 'reemplazo') ? 'none' : '';
                }
            }

            if (card) {
                const isRefuerzo = card.textContent.includes('REFUERZO');
                const requires = (status === 'constituye' || status === 'reemplazo' || isRefuerzo);
                card.setAttribute('data-requires-confirmation', requires ? '1' : '0');

                // Show/hide confirm box based on status
                const confirmWrap = document.getElementById('confirm-box-wrap-' + userId);
                if (confirmWrap) {
                    confirmWrap.classList.toggle('hidden', !requires);
                }

                if (!requires) {
                    card.setAttribute('data-is-confirmed', '0');
                    setConfirmState(userId, false);
                } else if (card.getAttribute('data-is-confirmed') === '1') {
                    setConfirmState(userId, true);
                } else {
                    setConfirmState(userId, false);
                }
            }

            refreshAttendanceSubmitButton();
        }

        window.openUndoReplacementModal = function(actionUrl) {
            const modal = document.getElementById('undoReplacementModal');
            const form = document.getElementById('undoReplacementModalForm');
            if (!modal || !form) return;
            form.action = actionUrl;
            modal.classList.remove('hidden');
        }

        window.closeUndoReplacementModal = function() {
            const modal = document.getElementById('undoReplacementModal');
            if (!modal) return;
            modal.classList.add('hidden');
        }

        @if(Auth::check() && Auth::user()->role === 'guardia')
            async function kioskPing() {
                try {
                    const res = await fetch('{{ route('kiosk.ping') }}', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });

                    if (res.status === 401) {
                        window.location.reload();
                        return;
                    }
                } catch (e) {
                    // noop
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                if (typeof window.bindAttendanceFormHandlers === 'function') {
                    window.bindAttendanceFormHandlers();
                }
            });

            kioskPing();
            setInterval(kioskPing, 60 * 1000);

            async function softRefreshGuardiaDashboard() {
                const root = document.getElementById('guardia-dashboard-root');
                if (!root) return;

                const activeEl = document.activeElement;
                const activeId = activeEl?.id;
                const activeName = activeEl?.getAttribute?.('name');
                const scrollY = window.scrollY;

                // Guardar estado de confirmación antes del swap
                const savedConfirmState = {};
                document.querySelectorAll('[data-card-user]').forEach(card => {
                    const uid = card.getAttribute('data-card-user');
                    if (!uid) return;
                    const cardEl = document.getElementById('guardia-card-' + uid);
                    const tokenEl = document.getElementById('confirm-token-' + uid);
                    savedConfirmState[uid] = {
                        confirmed: cardEl ? cardEl.getAttribute('data-is-confirmed') === '1' : false,
                        token: tokenEl ? tokenEl.value : '',
                    };
                });

                try {
                    const res = await fetch(window.location.href, {
                        method: 'GET',
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });

                    if (res.status === 401) {
                        window.location.reload();
                        return;
                    }

                    if (!res.ok) return;

                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const nextRoot = doc.getElementById('guardia-dashboard-root');
                    if (!nextRoot) return;

                    root.innerHTML = nextRoot.innerHTML;

                    // Reset dirty flag so markAttendanceDirty can re-fire if new items detected
                    window.__attendanceDirty = false;

                    // Restaurar estado de confirmación inmediatamente después del swap
                    Object.keys(savedConfirmState).forEach(uid => {
                        const state = savedConfirmState[uid];
                        const tokenEl = document.getElementById('confirm-token-' + uid);
                        if (tokenEl && state.token) tokenEl.value = state.token;
                        if (state.confirmed) {
                            setConfirmState(uid, true);
                        }
                    });

                    if (typeof window.bindAttendanceFormHandlers === 'function') {
                        window.bindAttendanceFormHandlers();
                    }

                    if (typeof window.__loadDraftState === 'function') {
                        await window.__loadDraftState();
                    }

                    window.scrollTo(0, scrollY);

                    if (activeId) {
                        const nextActive = document.getElementById(activeId);
                        if (nextActive && typeof nextActive.focus === 'function') nextActive.focus();
                    } else if (activeName) {
                        const esc = (window.CSS && typeof window.CSS.escape === 'function') ? window.CSS.escape : (v) => String(v).replace(/"/g, '\\"');
                        const nextActive = document.querySelector('[name="' + esc(activeName) + '"]');
                        if (nextActive && typeof nextActive.focus === 'function') nextActive.focus();
                    }
                } catch (e) {
                    // noop
                }
            }

            async function checkGuardiaUpdates() {
                try {
                    if (window.__attendanceDirty) return;
                    const res = await fetch('{{ route('guardia.snapshot') }}', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    });
                    if (!res.ok) return;

                    const data = await res.json();
                    if (!data || !data.ok) return;

                    const prev = window.__guardiaSnapshot || {};
                    const rosterChanged = (
                        (data.latest_bombero_at && data.latest_bombero_at !== prev.latest_bombero_at) ||
                        (data.latest_replacement_at && data.latest_replacement_at !== prev.latest_replacement_at)
                    );
                    const changed = (
                        (data.latest_novelty_at && data.latest_novelty_at !== prev.latest_novelty_at) ||
                        (data.latest_bombero_at && data.latest_bombero_at !== prev.latest_bombero_at) ||
                        (data.latest_replacement_at && data.latest_replacement_at !== prev.latest_replacement_at) ||
                        (data.latest_bed_assignment_at && data.latest_bed_assignment_at !== prev.latest_bed_assignment_at) ||
                        (data.attendance_saved_at && data.attendance_saved_at !== prev.attendance_saved_at) ||
                        (data.latest_draft_at && data.latest_draft_at !== prev.latest_draft_at)
                    );

                    if (changed) {
                        await softRefreshGuardiaDashboard();


                        window.__guardiaSnapshot = {
                            latest_novelty_at: data.latest_novelty_at,
                            latest_bombero_at: data.latest_bombero_at,
                            latest_replacement_at: data.latest_replacement_at,
                            latest_bed_assignment_at: data.latest_bed_assignment_at,
                            attendance_saved_at: data.attendance_saved_at,
                            latest_draft_at: data.latest_draft_at,
                        };
                        return;
                    }

                    window.__guardiaSnapshot = {
                        latest_novelty_at: data.latest_novelty_at,
                        latest_bombero_at: data.latest_bombero_at,
                        latest_replacement_at: data.latest_replacement_at,
                        latest_bed_assignment_at: data.latest_bed_assignment_at,
                        attendance_saved_at: data.attendance_saved_at,
                        latest_draft_at: data.latest_draft_at,
                    };
                } catch (e) {
                    // noop
                }
            }

            setInterval(checkGuardiaUpdates, 20 * 1000);
        @endif
    </script>

    <div id="undoReplacementModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="{{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-900 border-slate-800' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700' }} rounded-2xl shadow-2xl w-full max-w-sm mx-4 border overflow-hidden">
            <div class="p-4">
                <div class="text-sm font-bold {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-100' : 'text-slate-900' }} uppercase tracking-wider">Confirmar acción</div>
                <div class="mt-2 text-sm {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'text-slate-400' : 'text-slate-600 dark:text-slate-400' }}">¿Deshacer este reemplazo?</div>
            </div>
            <div class="p-4 pt-0 flex gap-2">
                <button type="button" onclick="closeUndoReplacementModal()" class="w-1/2 font-bold uppercase tracking-wider text-[10px] py-2 rounded-xl border {{ (Auth::check() && Auth::user()->role === 'guardia') ? 'bg-slate-950 hover:bg-slate-900 text-slate-100 border-slate-800' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-800 dark:text-white border-slate-200 dark:border-slate-700' }}">
                    Cancelar
                </button>
                <form id="undoReplacementModalForm" method="POST" class="w-1/2">
                    @csrf
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold uppercase tracking-wider text-[10px] py-2 rounded-xl border border-purple-700">
                        Confirmar
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(isset($myGuardia) && $myGuardia)
        <div id="replacementModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden z-50 flex items-center justify-center opacity-0 transition-opacity duration-300">
            <div class="bg-slate-900 text-slate-100 rounded-xl shadow-2xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 p-6 border border-slate-800">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-100 uppercase tracking-tight">Asignar Reemplazo</h3>
                        <p class="text-sm text-slate-400 mt-1">Selecciona el voluntario que cubrirá el turno.</p>
                    </div>
                    <button type="button" onclick="closeReplacementModal()" class="text-slate-400 hover:text-slate-200 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="bg-slate-950 border border-slate-800 rounded-lg p-3 mb-5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-blue-300 font-bold shrink-0">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-blue-300 uppercase tracking-wide">Reemplazando a:</span>
                        <p id="modal_original_user_name" class="text-sm font-bold text-slate-100">Usuario Original</p>
                    </div>
                </div>

                <form action="{{ route('admin.guardias.replacement') }}" method="POST">
                    @csrf
                    <input type="hidden" name="guardia_id" id="modal_guardia_id" value="{{ $myGuardia?->id }}">
                    <input type="hidden" name="original_firefighter_id" id="modal_original_firefighter_id">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-2">Voluntario Reemplazante</label>
                            <!-- Custom Professional Dropdown -->
                            <div class="relative" id="replacement-select-container">
                                <input type="hidden" name="replacement_firefighter_id" id="modal_replacement_firefighter_id" required>
                                
                                <!-- Search Input -->
                                <div class="relative">
                                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input type="text" id="replacement-search-input"
                                        class="w-full text-sm border-slate-800 rounded-lg shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 pl-9 pr-10 py-2.5 bg-slate-950 text-slate-100 placeholder:text-slate-500 dark:text-slate-400 cursor-pointer"
                                        placeholder="Buscar voluntario..." autocomplete="off" readonly>
                                    <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                </div>
                                
                                <!-- Dropdown Menu -->
                                <div id="replacement-dropdown" class="hidden absolute top-full left-0 right-0 mt-1 bg-slate-900 border border-slate-700 rounded-lg shadow-xl z-50 max-h-64 overflow-y-auto">
                                    <div class="p-2 sticky top-0 bg-slate-900 border-b border-slate-800">
                                        <input type="text" id="replacement-filter-input" 
                                            class="w-full text-xs bg-slate-800 border-slate-700 rounded px-2 py-1.5 text-slate-200 placeholder:text-slate-500 dark:text-slate-400 focus:outline-none focus:border-blue-500"
                                            placeholder="Filtrar por nombre o RUT...">
                                    </div>
                                    <div id="replacement-options-list" class="py-1">
                                        @foreach($replacementCandidates as $cand)
                                            <div class="replacement-option px-3 py-2 hover:bg-slate-800 cursor-pointer transition-colors flex items-center gap-3"
                                                 data-value="{{ $cand->id }}"
                                                 data-search="{{ strtolower(trim($cand->nombres . ' ' . $cand->apellido_paterno . ' ' . ($cand->apellido_materno ?? '') . ' ' . ($cand->rut ?? ''))) }}">
                                                <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 text-xs font-bold">
                                                    {{ strtoupper(substr($cand->nombres, 0, 1)) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-sm font-medium text-slate-200 truncate">
                                                        {{ trim($cand->nombres . ' ' . $cand->apellido_paterno) }}
                                                    </div>
                                                    @if($cand->rut)
                                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $cand->rut }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div id="replacement-no-results" class="hidden px-3 py-4 text-center text-xs text-slate-500 dark:text-slate-400">
                                        No se encontraron resultados
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" onclick="closeReplacementModal()" class="w-1/2 py-2.5 px-4 rounded-lg border border-slate-800 bg-slate-950 text-slate-100 font-bold text-sm hover:bg-slate-900 transition-colors uppercase">
                                Cancelar
                            </button>
                            <button type="submit" class="w-1/2 py-2.5 px-4 rounded-lg bg-blue-600 text-white font-bold text-sm hover:bg-blue-700 shadow-md hover:shadow-lg transition-all uppercase flex items-center justify-center gap-2">
                                <span>Confirmar</span>
                                <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
