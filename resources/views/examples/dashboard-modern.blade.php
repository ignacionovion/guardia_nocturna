@extends('layouts.modern')

@section('title', 'Dashboard - GuardiAPP')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Header con fecha y estado --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Panel de Control</h1>
            <p class="text-slate-500 dark:text-slate-400">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <x-ui.badge variant="success" size="md">
                <i class="fas fa-circle text-[8px] mr-1.5 animate-pulse"></i>
                Cuartel Operativo
            </x-ui.badge>
            <x-ui.button variant="primary" icon="fas fa-plus">
                Nueva Guardia
            </x-ui.button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-ui.stat-card 
            title="Bomberos en Guardia" 
            value="12" 
            icon="fas fa-users" 
            color="emerald"
            trend="8%"
            :trendUp="true" />
        
        <x-ui.stat-card 
            title="Camas Ocupadas" 
            value="8/15" 
            icon="fas fa-bed" 
            color="blue" />
        
        <x-ui.stat-card 
            title="Emergencias Hoy" 
            value="3" 
            icon="fas fa-truck-medical" 
            color="red" />
        
        <x-ui.stat-card 
            title="Novedades Pendientes" 
            value="5" 
            icon="fas fa-clipboard-list" 
            color="amber" />
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Guardia Actual --}}
        <div class="lg:col-span-2">
            <x-ui.card>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                        <i class="fas fa-shield text-red-600 mr-2"></i>
                        Guardia Actual
                    </h2>
                    <x-ui.button variant="ghost" size="sm" href="#">
                        Ver todos <i class="fas fa-arrow-right ml-1"></i>
                    </x-ui.button>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Ejemplo de tarjetas de bomberos --}}
                    <x-ui.firefighter-card 
                        :volunteer="(object)['nombre' => 'Juan Pérez', 'cargo' => 'Capitán', 'numero' => '15']"
                        status="constituye" />
                    
                    <x-ui.firefighter-card 
                        :volunteer="(object)['nombre' => 'María González', 'cargo' => 'Teniente', 'numero' => '23']"
                        status="constituye" />
                    
                    <x-ui.firefighter-card 
                        :volunteer="(object)['nombre' => 'Carlos Muñoz', 'cargo' => 'Voluntario', 'numero' => '45']"
                        status="reemplazo" />
                    
                    <x-ui.firefighter-card 
                        :volunteer="(object)['nombre' => 'Ana Silva', 'cargo' => 'Voluntario', 'numero' => '67']"
                        status="refuerzo" />
                </div>
            </x-ui.card>
        </div>

        {{-- Panel Lateral --}}
        <div class="space-y-6">
            {{-- Estado de Camas --}}
            <x-ui.card>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                        <i class="fas fa-bed text-blue-600 mr-2"></i>
                        Camas
                    </h2>
                    <span class="text-sm text-slate-500 dark:text-slate-400">8/15 ocupadas</span>
                </div>
                
                <div class="grid grid-cols-3 gap-2">
                    @for($i = 1; $i <= 6; $i++)
                    <x-ui.bed-card 
                        :bed="(object)['numero' => $i]"
                        :occupied="$i <= 4"
                        :volunteer="$i <= 4 ? (object)['nombre' => 'Bombero ' . $i] : null" />
                    @endfor
                </div>
                
                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <x-ui.button variant="outline" class="w-full" href="#">
                        <i class="fas fa-expand mr-2"></i>
                        Ver todas las camas
                    </x-ui.button>
                </div>
            </x-ui.card>

            {{-- Actividad Reciente --}}
            <x-ui.card>
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                    <i class="fas fa-clock-rotate-left text-purple-600 mr-2"></i>
                    Actividad Reciente
                </h2>
                
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center shrink-0">
                            <i class="fas fa-check text-emerald-600 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">Asistencia guardada</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hace 5 minutos</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center shrink-0">
                            <i class="fas fa-bed text-blue-600 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">Cama 3 asignada</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hace 15 minutos</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center shrink-0">
                            <i class="fas fa-people-arrows text-amber-600 text-xs"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">Reemplazo registrado</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hace 1 hora</p>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Tabla de Novedades --}}
    <x-ui.card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                <i class="fas fa-clipboard-list text-amber-600 mr-2"></i>
                Novedades del Día
            </h2>
            <div class="flex items-center gap-2">
                <x-ui.button variant="ghost" size="sm">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </x-ui.button>
                <x-ui.button variant="primary" size="sm" icon="fas fa-plus">
                    Nueva
                </x-ui.button>
            </div>
        </div>
        
        <x-ui.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Bombero</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Motivo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </x-slot:head>
            
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar name="Pedro Soto" size="sm" />
                        <div>
                            <div class="font-medium text-slate-900 dark:text-white">Pedro Soto</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">#34</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <x-ui.badge variant="purple">Permiso</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">Cita médica</td>
                <td class="px-4 py-3">
                    <x-ui.badge variant="warning">Pendiente</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-right">
                    <x-ui.button variant="ghost" size="xs">
                        <i class="fas fa-ellipsis-h"></i>
                    </x-ui.button>
                </td>
            </tr>
            
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar name="Luis Vera" size="sm" />
                        <div>
                            <div class="font-medium text-slate-900 dark:text-white">Luis Vera</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">#56</div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <x-ui.badge variant="info">Licencia</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">Licencia médica 3 días</td>
                <td class="px-4 py-3">
                    <x-ui.badge variant="success">Aprobado</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-right">
                    <x-ui.button variant="ghost" size="xs">
                        <i class="fas fa-ellipsis-h"></i>
                    </x-ui.button>
                </td>
            </tr>
        </x-ui.table>
    </x-ui.card>
</div>
@endsection
