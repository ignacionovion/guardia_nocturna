@extends('central.layouts.app')

@section('title', $body ? 'Editar Cuerpo' : 'Nuevo Cuerpo')

@section('content')
    <div class="mb-8">
        <a href="{{ route('central.bodies.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver a cuerpos</span>
        </a>
        <h1 class="text-2xl font-bold text-slate-900">{{ $body ? 'Editar Cuerpo' : 'Nuevo Cuerpo de Bomberos' }}</h1>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-8 max-w-2xl">
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $body ? route('central.bodies.update', $body) : route('central.bodies.store') }}">
            @csrf
            @if($body) @method('PUT') @endif

            <div class="space-y-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del Cuerpo</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $body?->nombre) }}" required
                           placeholder="Cuerpo de Bomberos de Temuco"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>

                <div>
                    <label for="ciudad" class="block text-sm font-medium text-slate-700 mb-1.5">Ciudad</label>
                    <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad', $body?->ciudad) }}"
                           placeholder="Temuco"
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>

                @if($body)
                <div class="flex items-center space-x-3">
                    <input type="hidden" name="activo" value="0">
                    <input type="checkbox" id="activo" name="activo" value="1"
                           {{ old('activo', $body->activo) ? 'checked' : '' }}
                           class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <label for="activo" class="text-sm text-slate-700">Cuerpo activo</label>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-end space-x-3 mt-8 pt-6 border-t border-slate-200">
                <a href="{{ route('central.bodies.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition">
                    {{ $body ? 'Guardar Cambios' : 'Crear Cuerpo' }}
                </button>
            </div>
        </form>
    </div>
@endsection
