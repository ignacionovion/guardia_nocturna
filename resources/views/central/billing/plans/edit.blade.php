@extends('central.layouts.app')

@section('title', 'Editar Plan - GuardiAPP SaaS')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Editar Plan: {{ $plan->nombre }}</h1>
            <p class="text-slate-600">Modifica la configuración del plan.</p>
        </div>
        <a href="{{ route('central.billing.plans.index') }}" class="text-slate-600 hover:text-slate-900">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Planes
        </a>
    </div>

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($plan->tenants()->count() > 0)
        <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg mb-6">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Este plan está asignado a {{ $plan->tenants()->count() }} tenant(s). Los cambios afectarán a todos los tenants con este plan.
        </div>
    @endif

    <form action="{{ route('central.billing.plans.update', $plan) }}" method="POST">
        @csrf
        @method('PATCH')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Información Básica --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Información Básica</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del Plan</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $plan->nombre) }}" 
                                   class="w-full rounded-lg border-slate-300" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Slug (identificador)</label>
                            <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" 
                                   class="w-full rounded-lg border-slate-300" required
                                   pattern="[a-z0-9\-]+" placeholder="basico, profesional, etc.">
                            <p class="text-xs text-slate-500 mt-1">Solo minúsculas, números y guiones</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                            <textarea name="descripcion" rows="2" 
                                      class="w-full rounded-lg border-slate-300">{{ old('descripcion', $plan->descripcion) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Precio Mensual ($)</label>
                            <input type="number" name="precio_mensual" value="{{ old('precio_mensual', $plan->precio_mensual) }}" 
                                   class="w-full rounded-lg border-slate-300" required min="0" step="0.01">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Precio Anual ($)</label>
                            <input type="number" name="precio_anual" value="{{ old('precio_anual', $plan->precio_anual) }}" 
                                   class="w-full rounded-lg border-slate-300" required min="0" step="0.01">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Orden de Visualización</label>
                            <input type="number" name="orden" value="{{ old('orden', $plan->orden) }}" 
                                   class="w-full rounded-lg border-slate-300" required min="0">
                        </div>

                        <div class="flex items-center">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="activo" value="1" {{ old('activo', $plan->activo) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                <span class="ml-2 text-sm font-medium text-slate-700">Plan Activo</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Límites --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Límites del Plan</h3>
                    <p class="text-sm text-slate-600 mb-4">Deja en blanco para límite ilimitado</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Máximo de Usuarios</label>
                            <input type="number" name="max_users" value="{{ old('max_users', $plan->max_users) }}" 
                                   class="w-full rounded-lg border-slate-300" min="1" placeholder="Ilimitado">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Máximo de Guardias</label>
                            <input type="number" name="max_guardias" value="{{ old('max_guardias', $plan->max_guardias) }}" 
                                   class="w-full rounded-lg border-slate-300" min="1" placeholder="Ilimitado">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Máximo de Camas</label>
                            <input type="number" name="max_beds" value="{{ old('max_beds', $plan->max_beds) }}" 
                                   class="w-full rounded-lg border-slate-300" min="1" placeholder="Ilimitado">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Almacenamiento (MB)</label>
                            <input type="number" name="max_storage_mb" value="{{ old('max_storage_mb', $plan->max_storage_mb) }}" 
                                   class="w-full rounded-lg border-slate-300" min="1" placeholder="Ilimitado">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Features y Addons --}}
            <div class="space-y-6">
                {{-- Módulos del Sistema --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Módulos del Sistema</h3>
                    
                    <div class="space-y-3">
                        @foreach($availableModules as $key => $label)
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="feature_{{ $key }}" value="1" 
                                       {{ old("feature_{$key}", $plan->hasModule($key)) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                <span class="ml-2 text-sm text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Addons SaaS --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Addons SaaS</h3>
                    
                    <div class="space-y-3">
                        @foreach($availableAddons as $key => $label)
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="addon_{{ $key }}" value="1" 
                                       {{ old("addon_{$key}", $plan->hasAddon($key)) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                <span class="ml-2 text-sm text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('central.billing.plans.index') }}" 
               class="px-4 py-2 text-slate-600 hover:text-slate-800 border border-slate-300 rounded-lg">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
                <i class="fas fa-save mr-2"></i>Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
