@extends('central.layouts.app')

@section('title', 'Administración Técnica - ' . $tenant->nombre)

@section('content')
    <div class="mb-6">
        <a href="{{ route('central.tenants.show', $tenant) }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver a {{ $tenant->nombre }}</span>
        </a>
    </div>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Administración Técnica</h1>
            <p class="text-slate-500 text-sm mt-1">{{ $tenant->nombre }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $tenant->estadoBadgeClass() }}">
            {{ $tenant->estadoLabel() }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Explorador de Datos --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900">Explorador de Datos</h2>
                    <p class="text-xs text-slate-400">Ver tablas y registros</p>
                </div>
            </div>
            <p class="text-sm text-slate-600 mb-4">Visualiza los datos directamente desde la base de datos del tenant. Solo lectura.</p>
            <a href="{{ route('central.tenants.explorer', $tenant) }}" class="block w-full text-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition text-sm font-medium">
                Abrir Explorador
            </a>
        </div>

        {{-- Reiniciar Base de Datos --}}
        <div class="bg-white rounded-2xl border border-amber-200 p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-amber-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900">Reiniciar Base de Datos</h2>
                    <p class="text-xs text-amber-600">⚠️ Destructivo</p>
                </div>
            </div>
            <p class="text-sm text-slate-600 mb-4">Elimina todas las tablas, recrea la base de datos y ejecuta migraciones + seeders.</p>
            <button onclick="document.getElementById('reset-modal').classList.remove('hidden')" class="block w-full text-center px-4 py-2 bg-amber-50 text-amber-700 rounded-lg hover:bg-amber-100 transition text-sm font-medium">
                Reiniciar Base de Datos
            </button>
        </div>

        {{-- Eliminar Compañía --}}
        <div class="bg-white rounded-2xl border border-red-200 p-6 md:col-span-2">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-red-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-slate-900">Eliminar Compañía Permanentemente</h2>
                    <p class="text-xs text-red-600">☠️ Irreversible</p>
                </div>
            </div>
            <div class="bg-red-50 border border-red-100 rounded-lg p-3 mb-4">
                <p class="text-sm text-red-700">Esta acción eliminará:</p>
                <ul class="text-sm text-red-600 list-disc list-inside mt-1">
                    <li>Registro de la compañía en el sistema central</li>
                    <li>Base de datos completa del tenant</li>
                    <li>Todos los backups asociados</li>
                    <li>Dominios configurados</li>
                </ul>
            </div>
            <button onclick="document.getElementById('delete-modal').classList.remove('hidden')" class="block w-full text-center px-4 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition text-sm font-medium">
                Eliminar Permanentemente
            </button>
        </div>
    </div>

    {{-- Modal: Reset Database --}}
    <div id="reset-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold text-slate-900 mb-2">Reiniciar Base de Datos</h3>
            <p class="text-sm text-slate-600 mb-4">
                Esto eliminará <strong>TODOS</strong> los datos de <strong>{{ $tenant->nombre }}</strong> y recreará la base de datos desde cero.
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                <p class="text-sm text-amber-800">Para confirmar, escribe el slug del tenant:</p>
                <p class="text-xs text-amber-600 font-mono mt-1">{{ $tenant->id }}</p>
            </div>
            <form method="POST" action="{{ route('central.tenants.reset-database', $tenant) }}">
                @csrf
                <input type="text" name="confirmation_slug" placeholder="Escribe el slug aquí" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm mb-4 focus:ring-2 focus:ring-amber-500 outline-none">
                <div class="flex space-x-3">
                    <button type="button" onclick="document.getElementById('reset-modal').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition text-sm font-medium">
                        Cancelar
                    </button>
                    <button type="submit" onclick="return confirm('¿ESTÁS SEGURO? Esta acción eliminará todos los datos permanentemente.')
                            class="flex-1 px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition text-sm font-medium">
                        Reiniciar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Delete Company --}}
    <div id="delete-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold text-red-900 mb-2">Eliminar Compañía Permanentemente</h3>
            <p class="text-sm text-slate-600 mb-4">
                Esto eliminará <strong>TODA</strong> la información de <strong>{{ $tenant->nombre }}</strong> incluyendo la base de datos, backups y registros.
            </p>
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                <p class="text-sm text-red-800">Para confirmar, escribe el slug del tenant:</p>
                <p class="text-xs text-red-600 font-mono mt-1">{{ $tenant->id }}</p>
            </div>
            <form method="POST" action="{{ route('central.tenants.destroy-completely', $tenant) }}">
                @csrf @method('DELETE')
                <input type="text" name="confirmation_slug" placeholder="Escribe el slug aquí" required
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm mb-4 focus:ring-2 focus:ring-red-500 outline-none">
                <div class="flex space-x-3">
                    <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition text-sm font-medium">
                        Cancelar
                    </button>
                    <button type="submit" onclick="return confirm('¿ESTÁS COMPLETAMENTE SEGURO? Esta acción es IRREVERSIBLE.')
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                        Eliminar Permanentemente
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
