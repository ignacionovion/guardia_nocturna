@extends('layouts.modern')

@section('content')
    <div class="max-w-5xl mx-auto">
        <x-ui.page-header title="Perfil del Voluntario" :subtitle="$volunteer->nombres . ' ' . $volunteer->apellido_paterno" icon="fas fa-user" iconVariant="blue">
            <div class="flex items-center gap-2">
                <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.volunteers.index') }}">
                    Volver al listado
                </x-ui.button>
                @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania'], true))
                    <x-ui.button variant="primary" size="md" icon="fas fa-edit" href="{{ route('admin.volunteers.edit', $volunteer->id) }}">
                        Editar
                    </x-ui.button>
                @endif
            </div>
        </x-ui.page-header>

        <div class="card-base overflow-hidden">
            <div class="p-8">
                {{-- Header con foto y datos principales --}}
                <div class="flex flex-col md:flex-row gap-6 mb-8">
                    {{-- Foto grande --}}
                    <div class="shrink-0">
                        @if($volunteer->photo_path)
                            <img src="{{ route('media', $volunteer->photo_path) }}" class="w-32 h-32 rounded-2xl object-cover border-2 border-slate-200 dark:border-slate-700 shadow-lg" alt="Foto de {{ $volunteer->nombres }}">
                        @else
                            <div class="w-32 h-32 rounded-2xl bg-slate-200 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-400 font-bold text-3xl border-2 border-slate-300 dark:border-slate-600 shadow-lg">
                                {{ substr($volunteer->nombres, 0, 1) }}{{ substr($volunteer->apellido_paterno, 0, 1) }}
                            </div>
                        @endif
                    </div>

                    {{-- Datos principales --}}
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
                            {{ $volunteer->nombres }} {{ $volunteer->apellido_paterno }} {{ $volunteer->apellido_materno }}
                        </h1>
                        <p class="text-lg text-slate-600 dark:text-slate-400 mt-1">
                            {{ $volunteer->cargo_texto ?: 'Bombero' }}
                        </p>
                        
                        {{-- Badges de estado --}}
                        <div class="flex flex-wrap gap-2 mt-3">
                            @if($volunteer->fuera_de_servicio)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                                    <i class="fas fa-ban"></i>
                                    Fuera de Servicio
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                    <i class="fas fa-check-circle"></i>
                                    Activo
                                </span>
                            @endif
                            
                            @if($volunteer->es_permanente)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                    <i class="fas fa-star"></i>
                                    Permanente
                                </span>
                            @endif
                            
                            @if($volunteer->es_jefe_guardia)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                    <i class="fas fa-crown"></i>
                                    Jefe de Guardia
                                </span>
                            @endif
                        </div>

                        {{-- Especialidades --}}
                        <div class="flex flex-wrap gap-2 mt-3">
                            @if($volunteer->es_conductor)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400 border border-sky-200 dark:border-sky-800">
                                    <i class="fas fa-car"></i>
                                    Conductor
                                    @if($volunteer->conductor_carros_bomba)
                                        <span class="ml-1 text-[10px]">(Carros Bomba)</span>
                                    @endif
                                </span>
                            @endif
                            
                            @if($volunteer->es_operador_rescate)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 border border-orange-200 dark:border-orange-800">
                                    <i class="fas fa-car-crash"></i>
                                    Operador Rescate
                                </span>
                            @endif
                            
                            @if($volunteer->es_asistente_trauma)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                    <i class="fas fa-medkit"></i>
                                    Asistente Trauma
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sección 1: Identificación Personal --}}
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="bg-blue-100 p-2 rounded-lg text-blue-700">
                            <i class="fas fa-id-card text-lg"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Identificación Personal</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">RUT</div>
                            <div class="text-base font-medium text-slate-800 dark:text-white">{{ $volunteer->rut ?: 'No registrado' }}</div>
                        </div>
                        
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Número de Registro</div>
                            <div class="text-base font-medium text-slate-800 dark:text-white">{{ $volunteer->numero_registro ?: 'No registrado' }}</div>
                        </div>
                        
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Portátil</div>
                            <div class="text-base font-medium text-slate-800 dark:text-white">{{ $volunteer->numero_portatil ?: 'No asignado' }}</div>
                        </div>
                        
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Email</div>
                            <div class="text-base font-medium text-slate-800 dark:text-white">{{ $volunteer->correo ?: 'No registrado' }}</div>
                        </div>
                        
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Fecha de Nacimiento</div>
                            <div class="text-base font-medium text-slate-800 dark:text-white">
                                @if($volunteer->fecha_nacimiento)
                                    {{ $volunteer->fecha_nacimiento->format('d/m/Y') }}
                                    <span class="text-xs text-slate-500 dark:text-slate-400 ml-1">({{ $volunteer->fecha_nacimiento->age }} años)</span>
                                @else
                                    No registrada
                                @endif
                            </div>
                        </div>
                        
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Fecha de Ingreso</div>
                            <div class="text-base font-medium text-slate-800 dark:text-white">
                                @if($volunteer->fecha_ingreso)
                                    {{ $volunteer->fecha_ingreso->format('d/m/Y') }}
                                @else
                                    No registrada
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección 2: Datos Institucionales --}}
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="bg-red-100 p-2 rounded-lg text-red-700">
                            <i class="fas fa-helmet-safety text-lg"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Datos Institucionales</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Guardia Asignada</div>
                            <div class="text-base font-medium text-slate-800 dark:text-white">
                                @if($volunteer->guardia)
                                    {{ $volunteer->guardia->name }}
                                @else
                                    Sin asignar
                                @endif
                            </div>
                        </div>
                        
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Cargo</div>
                            <div class="text-base font-medium text-slate-800 dark:text-white">{{ $volunteer->cargo_texto ?: 'Bombero' }}</div>
                        </div>
                        
                        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Estado de Asistencia</div>
                            <div class="text-base font-medium text-slate-800 dark:text-white capitalize">
                                @switch($volunteer->estado_asistencia)
                                    @case('constituye')
                                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">Constituye</span>
                                        @break
                                    @case('permiso')
                                        <span class="text-amber-600 dark:text-amber-400 font-bold">Permiso</span>
                                        @break
                                    @case('ausente')
                                        <span class="text-slate-600 dark:text-slate-400 font-bold">Ausente</span>
                                        @break
                                    @case('licencia')
                                        <span class="text-blue-600 dark:text-blue-400 font-bold">Licencia</span>
                                        @break
                                    @case('falta')
                                        <span class="text-red-600 dark:text-red-400 font-bold">Falta</span>
                                        @break
                                    @default
                                        <span class="text-slate-600 dark:text-slate-400 font-bold">{{ $volunteer->estado_asistencia }}</span>
                                @endswitch
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-slate-100 dark:border-slate-800">
                    <x-ui.button variant="secondary" size="md" href="{{ route('admin.volunteers.index') }}">
                        Volver al listado
                    </x-ui.button>
                    @if(in_array(auth()->user()->role, ['capitan', 'super_admin', 'capitania'], true))
                        <x-ui.button variant="primary" size="md" icon="fas fa-edit" href="{{ route('admin.volunteers.edit', $volunteer->id) }}">
                            Editar Voluntario
                        </x-ui.button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
