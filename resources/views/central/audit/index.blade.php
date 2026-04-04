@extends('central.layouts.app')

@section('title', 'Auditoría')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Auditoría Central</h1>
        <p class="text-slate-500 text-sm mt-1">Registro de todas las acciones realizadas en el panel de administración</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('central.audit.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Compañía</label>
                <select name="tenant_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                    <option value="">Todas</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ request('tenant_id') === $t->id ? 'selected' : '' }}>{{ $t->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Acción</label>
                <select name="action" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                    <option value="">Todas</option>
                    @foreach($actions as $key => $label)
                        <option value="{{ $key }}" {{ request('action') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Desde</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-slate-500 mb-1">Hasta</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['tenant_id', 'action', 'from', 'to']))
                    <a href="{{ route('central.audit.index') }}" class="px-4 py-2 text-sm text-slate-600 border border-slate-300 rounded-lg hover:bg-white transition">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Log entries --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="divide-y divide-slate-100">
            @forelse($logs as $log)
                <div class="px-6 py-4 hover:bg-white transition">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-3">
                            <span class="text-lg mt-0.5">{{ $log->actionIcon() }}</span>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $log->description }}</p>
                                <div class="flex items-center space-x-3 mt-1">
                                    @if($log->tenant_id)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-white text-slate-600">
                                            {{ $log->tenant_id }}
                                        </span>
                                    @endif
                                    <span class="text-xs text-slate-400">
                                        por {{ $log->admin_name }}
                                    </span>
                                    @if($log->ip_address)
                                        <span class="text-xs text-slate-300">{{ $log->ip_address }}</span>
                                    @endif
                                </div>
                                @if($log->metadata)
                                    <details class="mt-2">
                                        <summary class="text-xs text-slate-400 cursor-pointer hover:text-slate-600">Ver detalles</summary>
                                        <pre class="mt-1 text-[11px] bg-white p-2 rounded text-slate-600 overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @endif
                            </div>
                        </div>
                        <time class="text-xs text-slate-400 whitespace-nowrap ml-4" title="{{ $log->created_at }}">
                            {{ $log->created_at->diffForHumans() }}
                        </time>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-slate-400 text-sm">
                    No hay registros de auditoría aún.
                </div>
            @endforelse
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
