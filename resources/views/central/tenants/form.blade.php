@extends('central.layouts.app')

@section('title', $tenant ? 'Editar Compañía' : 'Nueva Compañía')

@section('content')
    <div class="mb-8">
        <a href="{{ route('central.tenants.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver a compañías</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900">{{ $tenant ? 'Editar Compañía' : 'Nueva Compañía' }}</h1>
        @if($tenant)
            <p class="text-slate-500 text-sm mt-1">Editando: {{ $tenant->nombre }} ({{ $tenant->id }})</p>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8 max-w-2xl">
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
                <p class="font-medium">{{ session('error') }}</p>
                @if(session('steps'))
                    <div class="mt-2 pt-2 border-t border-red-200">
                        <p class="text-xs font-medium text-red-600 mb-1">Pasos completados antes del error:</p>
                        <ul class="text-xs space-y-0.5">
                            @foreach(session('steps') as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $tenant ? route('central.tenants.update', $tenant) : route('central.tenants.store') }}">
            @csrf
            @if($tenant) @method('PUT') @endif

            <div class="space-y-6">
                @unless($tenant)
                <div>
                    <label for="id" class="block text-sm font-medium text-slate-700 mb-1.5">Slug (ID)</label>
                    <input type="text" id="id" name="id" value="{{ old('id') }}" required
                           pattern="[a-z0-9\-]+" placeholder="tercera-temuco"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                    <p class="text-xs text-slate-400 mt-1">Solo minúsculas, números y guiones. Será el subdominio y nombre de la base de datos.</p>
                </div>
                @endunless

                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre de la Compañía</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $tenant?->nombre) }}" required
                           placeholder="Tercera Compañía de Bomberos Temuco"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="numero" class="block text-sm font-medium text-slate-700 mb-1.5">Número</label>
                        <input type="number" id="numero" name="numero" value="{{ old('numero', $tenant?->numero) }}" min="1"
                               placeholder="3"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                    </div>
                    <div>
                        <label for="plan" class="block text-sm font-medium text-slate-700 mb-1.5">Plan</label>
                        <select id="plan" name="plan" required
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white">
                            <option value="basico" {{ old('plan', $tenant?->plan) === 'basico' ? 'selected' : '' }}>Básico</option>
                            <option value="profesional" {{ old('plan', $tenant?->plan) === 'profesional' ? 'selected' : '' }}>Profesional</option>
                            <option value="enterprise" {{ old('plan', $tenant?->plan) === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="body_id" class="block text-sm font-medium text-slate-700 mb-1.5">Cuerpo de Bomberos</label>
                    <select id="body_id" name="body_id"
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none bg-white">
                        <option value="">— Sin cuerpo —</option>
                        @foreach($bodies as $body)
                            <option value="{{ $body->id }}" {{ old('body_id', $tenant?->body_id) == $body->id ? 'selected' : '' }}>
                                {{ $body->nombre }} {{ $body->ciudad ? "({$body->ciudad})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="fecha_vencimiento" class="block text-sm font-medium text-slate-700 mb-1.5">Fecha de Vencimiento</label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento"
                           value="{{ old('fecha_vencimiento', $tenant?->fecha_vencimiento?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>

                @if($tenant)
                <div class="flex items-center space-x-3">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" id="activo" name="activo" value="1"
                           {{ old('activo', $tenant->activo) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <label for="activo" class="text-sm text-slate-700">Compañía activa</label>
                </div>
                @endif

                @unless($tenant)
                <div class="flex items-center space-x-3 bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <input type="checkbox" id="seed" name="seed" value="1" checked
                           class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <label for="seed" class="text-sm text-blue-800">
                        <span class="font-medium">Poblar con datos iniciales</span>
                        <span class="block text-xs text-blue-600 mt-0.5">Crea usuarios admin, camas, tareas de limpieza y configuración del sistema</span>
                    </label>
                </div>
                @endunless
            </div>

            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-slate-200">
                <a href="{{ route('central.tenants.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition">
                    {{ $tenant ? 'Guardar Cambios' : 'Crear Compañía' }}
                </button>
            </div>
        </form>
    </div>
@endsection
