<x-ui.page-container>
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[#1e293b] mb-1">Centro de Operaciones</h1>
                <p class="text-sm text-[#475569]">Panel de control operativo del sistema</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 border border-green-200 rounded-lg">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-semibold text-green-700">Sistema Operativo</span>
                </div>
                <div class="bg-slate-900 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-clock text-slate-400 text-xs"></i>
                    <span id="digital-clock" class="text-lg font-mono font-semibold">--:--:--</span>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-8">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                
                <!-- Métricas Principales -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-[#dde6ef] border border-[#9fb0c3] rounded-[14px] p-5 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="text-xs font-semibold text-[#475569] uppercase tracking-wide">Guardia en Servicio</div>
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-shield text-red-600"></i>
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-[#1e293b]">{{ $guardiaEnServicio?->name ?? 'Sin asignar' }}</div>
                    </div>

                    <div class="bg-[#dde6ef] border border-[#9fb0c3] rounded-[14px] p-5 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="text-xs font-semibold text-[#475569] uppercase tracking-wide">Personal en Guardia</div>
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-users text-blue-600"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-[#1e293b]">{{ $onDutyCount }}</div>
                    </div>

                    <div class="bg-[#dde6ef] border border-[#9fb0c3] rounded-[14px] p-5 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="text-xs font-semibold text-[#475569] uppercase tracking-wide">Camas Disponibles</div>
                            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-bed text-emerald-600"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-[#1e293b]">{{ $availableBeds }}<span class="text-xl text-[#475569] font-normal">/{{ $totalBeds }}</span></div>
                    </div>

                    <div class="bg-[#dde6ef] border border-[#9fb0c3] rounded-[14px] p-5 shadow-sm">
                        <div class="flex items-start justify-between mb-3">
                            <div class="text-xs font-semibold text-[#475569] uppercase tracking-wide">Novedades</div>
                            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-clipboard-list text-amber-600"></i>
                            </div>
                        </div>
                        <div class="text-3xl font-bold text-[#1e293b]">{{ $novelties->count() }}</div>
                    </div>
                </div>

                <!-- Panel de Movimientos -->
                <x-ui.card>
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-1">
                            <h2 class="text-xl font-semibold text-[#1e293b]">Movimientos de Personal</h2>
                            <span class="badge-neutral">{{ $activeReplacementsCount }} reemplazos</span>
                        </div>
                        <p class="text-sm text-[#475569]">Reemplazos, refuerzos y personal fuera de servicio</p>
                    </div>

                    <div>
                        <!-- Estadísticas Rápidas -->
                        @php
                            // Usar statusCounts del controlador (disponible para todos los roles)
                            // o calcular desde myStaff si está disponible (para compatibilidad)
                            $countPermiso    = $statusCounts['permiso'] ?? (isset($myStaff) ? $myStaff->where('estado_asistencia', 'permiso')->count() : 0);
                            $countAusente    = $statusCounts['ausente'] ?? (isset($myStaff) ? $myStaff->where('estado_asistencia', 'ausente')->count() : 0);
                            $countLicencia   = $statusCounts['licencia'] ?? (isset($myStaff) ? $myStaff->where('estado_asistencia', 'licencia')->count() : 0);
                            $countFalta      = $statusCounts['falta'] ?? (isset($myStaff) ? $myStaff->where('estado_asistencia', 'falta')->count() : 0);
                            $countInhabilita = $statusCounts['fuera_de_servicio'] ?? (isset($myStaff) ? $myStaff->where('fuera_de_servicio', true)->count() : 0);
                            $countConstituye = $statusCounts['constituye'] ?? (isset($myStaff) ? $myStaff->where('estado_asistencia', 'constituye')->count() : 0);
                            $dashboardActiveReplacements = isset($replacementByOriginal) ? collect($replacementByOriginal)->values() : collect();
                            $dashboardActiveRefuerzos = isset($activeStaff)
                                ? $activeStaff->filter(fn($staff) => (bool) ($staff->es_refuerzo ?? false))->values()
                                : collect();
                        @endphp
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="text-center p-4 bg-[#e7eef5] border border-[#9fb0c3] rounded-[14px]">
                                <div class="metric-value text-purple-600">{{ $activeReplacementsCount }}</div>
                                <div class="metric-label mt-1">Reemplazos</div>
                            </div>
                            <div class="text-center p-4 bg-[#e7eef5] border border-[#9fb0c3] rounded-[14px]">
                                <div class="metric-value text-sky-600">{{ $activeRefuerzosCount }}</div>
                                <div class="metric-label mt-1">Refuerzos</div>
                            </div>
                            <div class="text-center p-4 bg-[#e7eef5] border border-[#9fb0c3] rounded-[14px]">
                                <div class="metric-value text-red-600">{{ $outOfServiceFirefighters }}</div>
                                <div class="metric-label mt-1">Fuera de Servicio</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-5 gap-3 mb-6">
                            <div class="text-center p-3 bg-[#e7eef5] border border-[#9fb0c3] rounded-[14px]">
                                <div class="text-2xl font-black text-emerald-600">{{ $countConstituye }}</div>
                                <div class="metric-label mt-1">Constituyen</div>
                            </div>
                            <div class="text-center p-3 bg-[#e7eef5] border border-[#9fb0c3] rounded-[14px]">
                                <div class="text-2xl font-black text-amber-600">{{ $countPermiso }}</div>
                                <div class="metric-label mt-1">Permiso</div>
                            </div>
                            <div class="text-center p-3 bg-[#e7eef5] border border-[#9fb0c3] rounded-[14px]">
                                <div class="text-2xl font-black text-slate-600">{{ $countAusente }}</div>
                                <div class="metric-label mt-1">Ausente</div>
                            </div>
                            <div class="text-center p-3 bg-[#e7eef5] border border-[#9fb0c3] rounded-[14px]">
                                <div class="text-2xl font-black text-blue-600">{{ $countLicencia }}</div>
                                <div class="metric-label mt-1">Licencia</div>
                            </div>
                            <div class="text-center p-3 bg-[#e7eef5] border border-[#9fb0c3] rounded-[14px]">
                                <div class="text-2xl font-black text-red-600">{{ $countFalta }}</div>
                                <div class="metric-label mt-1">Falta</div>
                            </div>
                        </div>

                        @if($dashboardActiveReplacements->isNotEmpty())
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-[#1e293b] mb-4">Detalle de Reemplazos Activos</h3>
                                <div class="space-y-3">
                                    @foreach($dashboardActiveReplacements as $replacement)
                                        <div class="p-4 bg-[#e7eef5] rounded-[14px] border border-[#9fb0c3]">
                                            <div class="flex items-start gap-4">
                                                <!-- Reemplazado -->
                                                <div class="flex-1">
                                                    <div class="text-xs font-bold text-[#475569] uppercase tracking-wider mb-1">Titular Reemplazado</div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                                                            {{ substr($replacement->originalFirefighter?->nombres, 0, 1) }}{{ substr($replacement->originalFirefighter?->apellido_paterno, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-[#1e293b]">{{ $replacement->originalFirefighter?->nombres }} {{ $replacement->originalFirefighter?->apellido_paterno }}</div>
                                                            <div class="text-xs text-[#475569]">{{ $replacement->originalFirefighter?->rut ?? 'Sin RUT' }}</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Flecha -->
                                                <div class="pt-4">
                                                    <div class="bg-purple-100 p-2 rounded-full">
                                                        <i class="fas fa-arrow-right text-purple-600"></i>
                                                    </div>
                                                </div>

                                                <!-- Reemplazante -->
                                                <div class="flex-1">
                                                    <div class="text-xs font-bold text-[#475569] uppercase tracking-wider mb-1">Reemplazante</div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                                                            {{ substr($replacement->replacementFirefighter?->nombres, 0, 1) }}{{ substr($replacement->replacementFirefighter?->apellido_paterno, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-[#1e293b]">{{ $replacement->replacementFirefighter?->nombres }} {{ $replacement->replacementFirefighter?->apellido_paterno }}</div>
                                                            <div class="text-xs text-[#475569]">{{ $replacement->replacementFirefighter?->rut ?? 'Sin RUT' }}</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Info adicional -->
                                                <div class="text-right">
                                                    <div class="text-xs font-bold text-[#475569] uppercase tracking-wider mb-1">Inicio</div>
                                                    <div class="text-sm font-bold text-[#1e293b]">{{ $replacement->inicio?->format('d/m/Y H:i') ?? 'N/A' }}</div>
                                                    <button type="button" onclick="openUndoReplacementModal('{{ route('admin.guardias.replacement.undo', $replacement->id) }}')" class="mt-2 text-xs font-bold text-purple-600 hover:text-purple-800 underline">
                                                        Deshacer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($dashboardActiveRefuerzos->isNotEmpty())
                            <div class="mb-6">
                                <h3 class="text-sm font-semibold text-[#1e293b] mb-4">Detalle de Refuerzos Activos</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($dashboardActiveRefuerzos as $refuerzo)
                                        <div class="p-3 bg-[#e7eef5] rounded-[14px] border border-[#9fb0c3]">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-sm">
                                                    {{ substr($refuerzo->nombres, 0, 1) }}{{ substr($refuerzo->apellido_paterno, 0, 1) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-semibold text-[#1e293b] truncate">{{ $refuerzo->nombres }} {{ $refuerzo->apellido_paterno }}</div>
                                                    <div class="text-xs text-[#475569]">{{ $refuerzo->rut ?? 'Sin RUT' }}</div>
                                                </div>
                                                <span class="badge-info">Refuerzo</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($dashboardActiveReplacements->isEmpty() && $dashboardActiveRefuerzos->isEmpty())
                            <div class="text-center py-12">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-slate-100 rounded-full mb-3">
                                    <i class="fas fa-check text-slate-400"></i>
                                </div>
                                <p class="text-sm font-medium text-[#1e293b]">Sin movimientos activos</p>
                                <p class="text-sm text-[#475569] mt-1">No hay reemplazos ni refuerzos registrados</p>
                            </div>
                        @endif

                        @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
                            <div class="flex gap-3 pt-6 mt-6 border-t border-[#9fb0c3]">
                                <button onclick="openReplacementModal()" class="btn-primary flex-1">
                                    <i class="fas fa-plus"></i>
                                    Nuevo Reemplazo
                                </button>
                                <button onclick="openRefuerzoModal()" class="btn-primary flex-1 bg-sky-600 hover:bg-sky-700">
                                    <i class="fas fa-user-plus"></i>
                                    Agregar Refuerzo
                                </button>
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <!-- Resumen de Personal -->
                <x-ui.card>
                    <h2 class="text-xl font-semibold text-[#1e293b] mb-6">Resumen de Personal</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <p class="metric-value">{{ $totalFirefighters }}</p>
                            <p class="metric-label mt-1">Total Bomberos</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-emerald-600">{{ $activeFirefighters }}</p>
                            <p class="metric-label mt-1">Habilitados</p>
                        </div>
                        <div class="text-center">
                            <p class="metric-value">{{ $totalGuardias }}</p>
                            <p class="metric-label mt-1">Guardias</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-black text-amber-600">{{ $birthdaysMonthCount }}</p>
                            <p class="metric-label mt-1">Cumpleaños Mes</p>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Accesos Directos -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('admin.guardias') }}" class="group p-4 bg-[#0f172a] hover:bg-[#1e293b] rounded-[14px] transition-colors flex items-center gap-3 no-underline shadow-md">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-shield text-white"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-white">Guardias</p>
                            <p class="text-xs text-slate-400">Administrar equipos</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.dotaciones') }}" class="group p-4 bg-[#dde6ef] hover:bg-[#e7eef5] rounded-[14px] border border-[#9fb0c3] transition-colors flex items-center gap-3 no-underline shadow-sm">
                        <div class="w-10 h-10 bg-[#c3cfdb] rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-users-gear text-[#1e293b]"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-[#1e293b]">Dotaciones</p>
                            <p class="text-xs text-[#475569]">Asignar personal</p>
                        </div>
                    </a>

                    <a href="{{ route('camas') }}" class="group p-4 bg-[#dde6ef] hover:bg-[#e7eef5] rounded-[14px] border border-[#9fb0c3] transition-colors flex items-center gap-3 no-underline shadow-sm">
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-bed text-emerald-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-[#1e293b]">Camas</p>
                            <p class="text-xs text-[#475569]">{{ $availableBeds }} disponibles</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.emergencies.index') }}" class="group p-4 bg-[#dde6ef] hover:bg-[#e7eef5] rounded-[14px] border border-[#9fb0c3] transition-colors flex items-center gap-3 no-underline shadow-sm">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                            <i class="fas fa-truck-medical text-amber-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-sm text-[#1e293b]">Emergencias</p>
                            <p class="text-xs text-[#475569]">Ver historial</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="space-y-6">
                
                <!-- Estado del Turno -->
                <x-ui.card>
                    <h2 class="text-xl font-semibold text-[#1e293b] mb-4">Estado del Turno</h2>
                    @if($currentShift)
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <p class="font-semibold text-[#1e293b]">TURNO ACTIVO</p>
                        </div>
                        <p class="text-sm text-[#475569] mb-1">Guardia constituida y operativa</p>
                        <p class="text-xs text-[#475569]">Inicio: {{ $currentShift->created_at?->format('H:i') ?? '--:--' }}</p>
                    @else
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                            <p class="font-semibold text-amber-700">SIN CONSTITUIR</p>
                        </div>
                        <p class="text-sm text-[#475569]">La guardia aún no ha sido constituida</p>
                    @endif
                </x-ui.card>

                <!-- Próximos Cumpleaños -->
                <x-ui.card>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-[#1e293b]">Cumpleaños</h2>
                        <span class="badge-warning">{{ $birthdaysMonthCount }} este mes</span>
                    </div>
                    <div>
                        @if($upcomingBirthdaysAll->isEmpty())
                            <p class="text-sm text-[#475569] text-center">No hay cumpleaños próximos</p>
                        @else
                            <div class="space-y-3">
                                @foreach($upcomingBirthdaysAll as $b)
                                    <div class="flex items-center gap-3 p-2.5 rounded-lg bg-[#e7eef5] border border-[#9fb0c3]">
                                        <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-800 flex items-center justify-center font-bold text-xs border border-amber-300">
                                            {{ strtoupper(substr($b->nombres, 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-bold text-slate-900 truncate">{{ $b->nombres }} {{ $b->apellido_paterno }}</div>
                                            <div class="text-xs font-semibold text-slate-600">{{ $b->next_birthday->format('d') }} de {{ $b->next_birthday->locale('es')->monthName }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                <!-- Últimas Novedades -->
                <x-ui.card>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-[#1e293b]">Novedades Recientes</h2>
                        <button onclick="openNoveltyModal()" class="text-xs font-semibold text-blue-600 hover:text-blue-800">Registrar</button>
                    </div>
                    <div>
                        @if($novelties->isEmpty())
                            <p class="text-sm text-[#475569] text-center">Sin novedades recientes</p>
                        @else
                            <div class="space-y-4">
                                @foreach($novelties->take(3) as $novelty)
                                    @php
                                        $noveltyColors = [
                                            'Informativa' => ['text' => 'text-blue-600', 'bgLight' => 'bg-blue-50', 'border' => 'border-blue-200'],
                                            'Incidente' => ['text' => 'text-amber-600', 'bgLight' => 'bg-amber-50', 'border' => 'border-amber-200'],
                                            'Mantención' => ['text' => 'text-emerald-600', 'bgLight' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
                                            'Urgente' => ['text' => 'text-red-600', 'bgLight' => 'bg-red-50', 'border' => 'border-red-200'],
                                            'Permanente' => ['text' => 'text-purple-600', 'bgLight' => 'bg-purple-50', 'border' => 'border-purple-200'],
                                        ];
                                        $colors = $noveltyColors[$novelty->type] ?? $noveltyColors['Informativa'];
                                        $canDeleteNovelty = in_array(auth()->user()->role ?? null, ['capitan', 'super_admin', 'capitania']) || 
                                            ($novelty->user_id === auth()->id() && !$novelty->is_permanent);
                                    @endphp
                                    <div class="border-l-2 border-slate-300 dark:border-slate-600 pl-3 py-2 relative group">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-xs font-bold {{ $colors['text'] }} {{ $colors['bgLight'] }} px-2 py-0.5 rounded border {{ $colors['border'] }}">{{ $novelty->type }}</span>
                                            @if($novelty->is_permanent && mb_strtolower((string) $novelty->type) !== 'permanente')
                                                <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded border border-purple-200">PERMANENTE</span>
                                            @endif
                                            @if($canDeleteNovelty)
                                                <button onclick="deleteNovelty({{ $novelty->id }})" class="ml-auto text-xs text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <p class="font-bold text-slate-900 text-sm">{{ $novelty->title }}</p>
                                        <p class="text-xs text-slate-500 mt-1">{{ $novelty->created_at->locale('es')->diffForHumans() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-ui.page-container>
