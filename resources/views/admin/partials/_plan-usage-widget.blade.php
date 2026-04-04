@props(['planUsage'])

<div class="bg-white rounded-xl border border-slate-200">
    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Consumo del Plan</h3>
            <p class="text-sm text-slate-600">Uso actual de los recursos de tu plan.</p>
        </div>
        @if(isset($planUsage['plan_name']))
            <span class="px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800 border border-blue-200">
                {{ $planUsage['plan_name'] }}
            </span>
        @endif
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        {{-- Usuarios --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-medium text-slate-700">Usuarios</p>
                <p class="text-sm font-bold text-slate-900">
                    {{ $planUsage['users']['current'] }}
                    @if(!$planUsage['users']['unlimited'])
                        / {{ $planUsage['users']['limit'] }}
                    @else
                        <i class="fas fa-infinity text-slate-500"></i>
                    @endif
                </p>
            </div>
            @if(!$planUsage['users']['unlimited'])
                <div class="w-full bg-white rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" 
                         style="width: {{ $planUsage['users']['limit'] > 0 ? ($planUsage['users']['current'] / $planUsage['users']['limit']) * 100 : 0 }}%">
                    </div>
                </div>
            @endif
        </div>

        {{-- Camas --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-medium text-slate-700">Camas</p>
                <p class="text-sm font-bold text-slate-900">
                    {{ $planUsage['beds']['current'] }}
                    @if(!$planUsage['beds']['unlimited'])
                        / {{ $planUsage['beds']['limit'] }}
                    @else
                        <i class="fas fa-infinity text-slate-500"></i>
                    @endif
                </p>
            </div>
            @if(!$planUsage['beds']['unlimited'])
                <div class="w-full bg-white rounded-full h-2">
                    <div class="bg-emerald-600 h-2 rounded-full" 
                         style="width: {{ $planUsage['beds']['limit'] > 0 ? ($planUsage['beds']['current'] / $planUsage['beds']['limit']) * 100 : 0 }}%">
                    </div>
                </div>
            @endif
        </div>

        {{-- Guardias (mes) --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-medium text-slate-700">Guardias (este mes)</p>
                <p class="text-sm font-bold text-slate-900">
                    {{ $planUsage['guardias']['current'] }}
                    @if(!$planUsage['guardias']['unlimited'])
                        / {{ $planUsage['guardias']['limit'] }}
                    @else
                        <i class="fas fa-infinity text-slate-500"></i>
                    @endif
                </p>
            </div>
            @if(!$planUsage['guardias']['unlimited'])
                <div class="w-full bg-white rounded-full h-2">
                    <div class="bg-violet-600 h-2 rounded-full" 
                         style="width: {{ $planUsage['guardias']['limit'] > 0 ? ($planUsage['guardias']['current'] / $planUsage['guardias']['limit']) * 100 : 0 }}%">
                    </div>
                </div>
            @endif
        </div>

        {{-- Almacenamiento --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-medium text-slate-700">Almacenamiento</p>
                <p class="text-sm font-bold text-slate-900">
                    {{ number_format($planUsage['storage_mb']['current'], 0) }} MB
                    @if(!$planUsage['storage_mb']['unlimited'])
                        / {{ $planUsage['storage_mb']['limit'] }} MB
                    @else
                        <i class="fas fa-infinity text-slate-500"></i>
                    @endif
                </p>
            </div>
            @if(!$planUsage['storage_mb']['unlimited'])
                <div class="w-full bg-white rounded-full h-2">
                    <div class="bg-amber-500 h-2 rounded-full" 
                         style="width: {{ $planUsage['storage_mb']['limit'] > 0 ? ($planUsage['storage_mb']['current'] / $planUsage['storage_mb']['limit']) * 100 : 0 }}%">
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
