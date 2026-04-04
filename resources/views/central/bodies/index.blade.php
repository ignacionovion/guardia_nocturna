@extends('central.layouts.app')

@section('title', 'Cuerpos de Bomberos')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Cuerpos de Bomberos</h1>
            <p class="text-slate-500 text-sm mt-1">Agrupaciones de compañías</p>
        </div>
        <a href="{{ route('central.bodies.create') }}"
           class="bg-slate-900 text-white text-sm font-medium py-2.5 px-5 rounded-xl hover:bg-slate-800 transition flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Nuevo Cuerpo</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-white border-b border-slate-200">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Cuerpo</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Ciudad</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Compañías</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($bodies as $body)
                    <tr class="hover:bg-white transition">
                        <td class="px-6 py-4">
                            <p class="font-medium text-slate-900 text-sm">{{ $body->nombre }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $body->ciudad ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white text-slate-600">
                                {{ $body->tenants_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $body->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                {{ $body->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('central.bodies.edit', $body) }}" class="text-slate-400 hover:text-amber-600 transition" title="Editar">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                @if($body->tenants_count === 0)
                                <form method="POST" action="{{ route('central.bodies.destroy', $body) }}"
                                      onsubmit="return confirm('¿Eliminar este cuerpo?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-600 transition" title="Eliminar">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                            No hay cuerpos registrados. <a href="{{ route('central.bodies.create') }}" class="text-blue-600 hover:underline">Crear el primero</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($bodies->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $bodies->links() }}
            </div>
        @endif
    </div>
@endsection
