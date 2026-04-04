@extends('central.layouts.app')

@section('title', 'Backups')

@section('content')
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Backups</h1>
            <p class="text-slate-500 text-sm mt-1">Historial de respaldos de bases de datos de tenants</p>
        </div>
        <div class="flex items-center space-x-3">
            {{-- Backup specific tenant --}}
            <form method="POST" action="{{ route('central.backups.store') }}" class="flex items-center space-x-2">
                @csrf
                <select name="tenant_id" class="px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="">Todos los tenants</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-slate-900 text-white text-sm font-medium py-2 px-4 rounded-lg hover:bg-slate-800 transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Crear Backup</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-6">
        <form method="GET" action="{{ route('central.backups.index') }}" class="flex items-end gap-4">
            <div class="flex-1 max-w-xs">
                <label class="block text-xs font-medium text-slate-500 mb-1">Filtrar por compañía</label>
                <select name="tenant_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="">Todas</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ $filterTenant === $t->id ? 'selected' : '' }}>{{ $t->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">Filtrar</button>
            @if($filterTenant)
                <a href="{{ route('central.backups.index') }}" class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-white transition">Limpiar</a>
            @endif
        </form>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase">Total Backups</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ count($backups) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase">Tamaño Total</p>
            @php $totalSize = array_sum(array_column($backups, 'size')); @endphp
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalSize > 0 ? round($totalSize / 1024 / 1024, 1) . ' MB' : '0 B' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <p class="text-xs font-medium text-slate-500 uppercase">Último Backup</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ count($backups) > 0 ? $backups[0]['date_human'] : '—' }}</p>
        </div>
    </div>

    {{-- Backup list --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-white border-b border-slate-200">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Archivo</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tenant</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tamaño</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($backups as $backup)
                    <tr class="hover:bg-white transition">
                        <td class="px-6 py-3">
                            <code class="text-xs bg-white px-2 py-1 rounded text-slate-600">{{ $backup['filename'] }}</code>
                        </td>
                        <td class="px-6 py-3 text-sm text-slate-600">
                            {{ $backup['tenant_id'] ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-sm text-slate-600">
                            {{ $backup['size_formatted'] }}
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-sm text-slate-600">{{ $backup['date'] }}</span>
                            <span class="text-xs text-slate-400 ml-1">({{ $backup['date_human'] }})</span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                {{-- Download --}}
                                <a href="{{ route('central.backups.download', ['file' => $backup['filename']]) }}"
                                   class="text-slate-400 hover:text-blue-600 transition" title="Descargar">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                </a>
                                {{-- Restore --}}
                                <form method="POST" action="{{ route('central.backups.restore') }}"
                                      onsubmit="return confirm('⚠️ ¿Restaurar este backup?\n\nEsto SOBRESCRIBIRÁ la base de datos {{ $backup['database'] }} con los datos del backup.\n\nEsta acción NO se puede deshacer.')">
                                    @csrf
                                    <input type="hidden" name="file" value="{{ $backup['filename'] }}">
                                    <button type="submit" class="text-slate-400 hover:text-amber-600 transition" title="Restaurar">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                </form>
                                {{-- Delete --}}
                                <form method="POST" action="{{ route('central.backups.destroy') }}"
                                      onsubmit="return confirm('¿Eliminar este backup?')">
                                    @csrf @method('DELETE')
                                    <input type="hidden" name="file" value="{{ $backup['filename'] }}">
                                    <button type="submit" class="text-slate-400 hover:text-red-600 transition" title="Eliminar">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                            No hay backups disponibles. Crea uno usando el botón de arriba.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Info --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
        <p class="font-medium">ℹ️ Información de Backups</p>
        <ul class="mt-2 text-xs text-blue-700 space-y-1">
            <li>• Los backups automáticos se ejecutan diariamente a las <strong>03:00</strong>.</li>
            <li>• Se retienen por <strong>7 días</strong> por defecto (configurable con <code>--keep</code>).</li>
            <li>• La restauración <strong>sobrescribe</strong> completamente la base de datos del tenant.</li>
            <li>• Ubicación: <code>storage/app/backups/</code></li>
        </ul>
    </div>
@endsection
