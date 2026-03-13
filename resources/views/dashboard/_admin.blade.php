        <!-- VISTA ADMIN / GENERAL - DASHBOARD PROFESIONAL -->
        
        <x-ui.page-header title="Centro de Operaciones" subtitle="Panel de control operativo del sistema" icon="fas fa-gauge-high" iconVariant="red">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-4 py-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-bold text-green-700 dark:text-green-400 uppercase tracking-wider">Sistema Operativo</span>
                </div>
                <div class="bg-slate-900 text-white px-5 py-2.5 rounded-lg border border-slate-700 flex items-center gap-3">
                    <i class="fas fa-clock text-slate-400 text-sm"></i>
                    <span id="digital-clock" class="text-xl font-mono font-bold tracking-wider">--:--:--</span>
                </div>
            </div>
        </x-ui.page-header>

        <!-- Grid Principal: KPIs y Accesos -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
            
            <!-- Columna Izquierda: Estado Operativo -->
            <div class="xl:col-span-2 space-y-6">
                
                <!-- Tarjetas KPI Principales -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <x-ui.stat-card
                        title="Guardia en Servicio"
                        :value="$guardiaEnServicio?->name ?? 'Sin asignar'"
                        icon="fas fa-shield"
                        color="red"
                    />

                    <x-ui.stat-card
                        title="Personal en Guardia Nocturna"
                        :value="$onDutyCount"
                        icon="fas fa-users"
                        color="blue"
                    />

                    <x-ui.stat-card
                        title="Camas Libres"
                        :value="$availableBeds . '/' . $totalBeds"
                        icon="fas fa-bed"
                        color="emerald"
                    />

                    <x-ui.stat-card
                        title="Novedades"
                        :value="$novelties->count()"
                        icon="fas fa-clipboard-list"
                        color="amber"
                    />
                </div>

                <!-- Panel de Movimientos - Estilo Reporte Profesional -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-purple-600 p-2 rounded-lg text-white shadow-sm">
                                    <i class="fas fa-exchange-alt text-lg"></i>
                                </div>
                                <div>
                                    <h2 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-sm">Reporte de Movimientos</h2>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Reemplazos, refuerzos y personal fuera de servicio</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Total reemplazos:</span>
                                <span class="text-xl font-bold text-purple-700 dark:text-purple-400">{{ $activeReplacementsCount }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
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
                            <x-ui.metric-card
                                :value="$activeReplacementsCount"
                                label="Reemplazos"
                                icon="fas fa-exchange-alt"
                                variant="purple"
                            />
                            <x-ui.metric-card
                                :value="$activeRefuerzosCount"
                                label="Refuerzos"
                                icon="fas fa-user-plus"
                                variant="sky"
                            />
                            <x-ui.metric-card
                                :value="$outOfServiceFirefighters"
                                label="Fuera de Servicio"
                                icon="fas fa-ban"
                                variant="danger"
                            />
                        </div>
                        <div class="grid grid-cols-5 gap-3 mb-6">
                            <x-ui.metric-card
                                :value="$countConstituye"
                                label="Constituyen"
                                icon="fas fa-check-circle"
                                variant="success"
                            />
                            <x-ui.metric-card
                                :value="$countPermiso"
                                label="Permiso"
                                icon="fas fa-clipboard"
                                variant="warning"
                            />
                            <x-ui.metric-card
                                :value="$countAusente"
                                label="Ausente"
                                icon="fas fa-minus-circle"
                                variant="default"
                            />
                            <x-ui.metric-card
                                :value="$countLicencia"
                                label="Licencia"
                                icon="fas fa-file-medical"
                                variant="primary"
                            />
                            <x-ui.metric-card
                                :value="$countFalta"
                                label="Falta"
                                icon="fas fa-times-circle"
                                variant="danger"
                            />
                        </div>

                        <!-- Lista Detallada de Reemplazos Activos -->
                        <!-- Lista Detallada de Reemplazos Activos -->
                        <!-- Lista Detallada de Reemplazos Activos -->
                        <!-- Lista Detallada de Reemplazos Activos -->
                        @if($dashboardActiveReplacements->isNotEmpty())
                            <div class="mb-6">
                                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fas fa-list-ul"></i>
                                    Detalle de Reemplazos Activos
                                </h3>
                                <div class="space-y-3">
                                    @foreach($dashboardActiveReplacements as $replacement)
                                        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                                            <div class="flex items-start gap-4">
                                                <!-- Reemplazado -->
                                                <div class="flex-1">
                                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Titular Reemplazado</div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                                                            {{ substr($replacement->originalFirefighter?->nombres, 0, 1) }}{{ substr($replacement->originalFirefighter?->apellido_paterno, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-slate-900 dark:text-white">{{ $replacement->originalFirefighter?->nombres }} {{ $replacement->originalFirefighter?->apellido_paterno }}</div>
                                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $replacement->originalFirefighter?->rut ?? 'Sin RUT' }}</div>
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
                                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Reemplazante</div>
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                                                            {{ substr($replacement->replacementFirefighter?->nombres, 0, 1) }}{{ substr($replacement->replacementFirefighter?->apellido_paterno, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-slate-900 dark:text-white">{{ $replacement->replacementFirefighter?->nombres }} {{ $replacement->replacementFirefighter?->apellido_paterno }}</div>
                                                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $replacement->replacementFirefighter?->rut ?? 'Sin RUT' }}</div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Info adicional -->
                                                <div class="text-right">
                                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Inicio</div>
                                                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $replacement->inicio?->format('d/m/Y H:i') ?? 'N/A' }}</div>
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

                        <!-- Lista Detallada de Refuerzos Activos -->
                        @if($dashboardActiveRefuerzos->isNotEmpty())
                            <div class="mb-6">
                                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fas fa-user-friends"></i>
                                    Detalle de Refuerzos Activos
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($dashboardActiveRefuerzos as $refuerzo)
                                        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400 flex items-center justify-center font-bold text-sm">
                                                    {{ substr($refuerzo->nombres, 0, 1) }}{{ substr($refuerzo->apellido_paterno, 0, 1) }}
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-semibold text-slate-900 dark:text-white truncate">{{ $refuerzo->nombres }} {{ $refuerzo->apellido_paterno }}</div>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $refuerzo->rut ?? 'Sin RUT' }}</div>
                                                </div>
                                                <span class="text-[10px] font-semibold uppercase tracking-wider text-sky-700 dark:text-sky-400 bg-sky-100 dark:bg-sky-900/30 px-2 py-1 rounded">Refuerzo</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($dashboardActiveReplacements->isEmpty() && $dashboardActiveRefuerzos->isEmpty())
                            <div class="text-center py-8 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-slate-200 rounded-full mb-3">
                                    <i class="fas fa-check text-slate-500 dark:text-slate-400"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-600 dark:text-slate-400">Sin movimientos activos</p>
                                <p class="text-xs text-slate-400 mt-1">No hay reemplazos ni refuerzos registrados</p>
                            </div>
                        @endif

                        <!-- Acciones Rápidas -->
                        @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
                            <div class="flex gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                                <button onclick="openReplacementModal()" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 px-4 rounded-lg text-xs uppercase tracking-wider transition-all shadow-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-plus"></i>
                                    Nuevo Reemplazo
                                </button>
                                <button onclick="openRefuerzoModal()" class="flex-1 bg-sky-600 hover:bg-sky-700 text-white font-bold py-2.5 px-4 rounded-lg text-xs uppercase tracking-wider transition-all shadow-sm flex items-center justify-center gap-2">
                                    <i class="fas fa-user-plus"></i>
                                    Agregar Refuerzo
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Resumen de Personal -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                        <h2 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-sm flex items-center gap-2">
                            <i class="fas fa-users text-slate-500 dark:text-slate-400"></i>
                            Resumen de Personal
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            <div class="text-center">
                                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $totalFirefighters }}</p>
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Total Bomberos</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold text-emerald-600">{{ $activeFirefighters }}</p>
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Habilitados</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ $totalGuardias }}</p>
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Guardias</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold text-amber-600">{{ $birthdaysMonthCount }}</p>
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-1">Cumpleaños Mes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accesos Directos -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <a href="{{ route('admin.guardias') }}" class="group bg-slate-900 hover:bg-slate-800 text-white rounded-xl p-4 shadow-sm transition-all flex items-center gap-3">
                        <div class="bg-white/10 p-2 rounded-lg">
                            <i class="fas fa-shield"></i>
                        </div>
                        <div>
                            <p class="font-bold text-sm uppercase">Guardias</p>
                            <p class="text-xs text-slate-400">Administrar equipos</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.dotaciones') }}" class="group bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm transition-all flex items-center gap-3">
                        <div class="bg-slate-100 dark:bg-slate-800 p-2 rounded-lg text-slate-600 dark:text-slate-400">
                            <i class="fas fa-users-gear"></i>
                        </div>
                        <div>
                            <p class="font-bold text-sm uppercase">Dotaciones</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Asignar personal</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('camas') }}" class="group bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm transition-all flex items-center gap-3">
                        <div class="bg-emerald-100 p-2 rounded-lg text-emerald-600">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div>
                            <p class="font-bold text-sm uppercase">Camas</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $availableBeds }} disponibles</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.emergencies.index') }}" class="group bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-xl p-4 shadow-sm transition-all flex items-center gap-3">
                        <div class="bg-amber-100 p-2 rounded-lg text-amber-600">
                            <i class="fas fa-truck-medical"></i>
                        </div>
                        <div>
                            <p class="font-bold text-sm uppercase">Emergencias</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Ver historial</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Columna Derecha: Información y Alertas -->
            <div class="space-y-6">
                
                <!-- Estado del Turno -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                        <h2 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-xs flex items-center gap-2">
                            <i class="fas fa-info-circle text-slate-500 dark:text-slate-400"></i>
                            Estado del Turno
                        </h2>
                    </div>
                    <div class="p-5">
                        @if($currentShift)
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <p class="font-bold text-slate-900 dark:text-white">TURNO ACTIVO</p>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">Guardia constituida y operativa</p>
                            <p class="text-xs text-slate-400">Inicio: {{ $currentShift->created_at?->format('H:i') ?? '--:--' }}</p>
                        @else
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                                <p class="font-bold text-amber-700 dark:text-amber-400">SIN CONSTITUIR</p>
                            </div>
                            <p class="text-sm text-slate-600 dark:text-slate-400">La guardia aún no ha sido constituida</p>
                        @endif
                    </div>
                </div>

                <!-- Próximos Cumpleaños -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 flex justify-between items-center">
                        <h2 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-xs flex items-center gap-2">
                            <i class="fas fa-cake-candles text-amber-600"></i>
                            Cumpleaños
                        </h2>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $birthdaysMonthCount }} este mes</span>
                    </div>
                    <div class="p-5">
                        @if($upcomingBirthdaysAll->isEmpty())
                            <p class="text-sm text-slate-500 dark:text-slate-400 text-center">No hay cumpleaños próximos</p>
                        @else
                            <div class="space-y-3">
                                @foreach($upcomingBirthdaysAll as $b)
                                    <div class="flex items-center gap-3 p-2.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                        <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-800 flex items-center justify-center font-bold text-xs border border-amber-300">
                                            {{ strtoupper(substr($b->nombres, 0, 1)) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $b->nombres }} {{ $b->apellido_paterno }}</div>
                                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $b->next_birthday->format('d') }} de {{ $b->next_birthday->locale('es')->monthName }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Últimas Novedades -->
                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 flex justify-between items-center">
                        <h2 class="font-bold text-slate-900 dark:text-white uppercase tracking-wider text-xs flex items-center gap-2">
                            <i class="fas fa-bullhorn text-slate-500 dark:text-slate-400"></i>
                            Novedades Recientes
                        </h2>
                        <button onclick="openNoveltyModal()" class="text-xs font-bold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 uppercase">Registrar</button>
                    </div>
                    <div class="p-5">
                        @if($novelties->isEmpty())
                            <p class="text-sm text-slate-500 dark:text-slate-400 text-center">Sin novedades recientes</p>
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
                                        $canDeleteNovelty = in_array(auth()->user()->role ?? null, ['super_admin', 'capitania']) || 
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
                                        <p class="font-bold text-slate-900 dark:text-white text-sm">{{ $novelty->title }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $novelty->created_at->locale('es')->diffForHumans() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

