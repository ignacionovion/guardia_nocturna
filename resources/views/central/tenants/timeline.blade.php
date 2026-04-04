@extends('central.layouts.app')

@section('title', 'Timeline - ' . $tenant->nombre)

@section('content')
    <div class="mb-6">
        <a href="{{ route('central.tenants.show', $tenant) }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver a {{ $tenant->nombre }}</span>
        </a>
    </div>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Timeline de {{ $tenant->nombre }}</h1>
            <p class="text-slate-500 text-sm mt-1">Historial completo de eventos y cambios</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $tenant->estadoBadgeClass() }}">
            {{ $tenant->estadoLabel() }}
        </span>
    </div>

    {{-- Timeline --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        @if($events->isEmpty())
            <div class="text-center py-12 text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>No hay eventos registrados aún.</p>
            </div>
        @else
            <div class="relative">
                {{-- Vertical line --}}
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-200"></div>

                <div class="space-y-6">
                    @foreach($events as $event)
                        <div class="relative flex items-start space-x-4 pl-10">
                            {{-- Icon dot --}}
                            <div class="absolute left-0 w-8 h-8 rounded-full flex items-center justify-center text-lg
                                @switch($event->action)
                                    @case('tenant_created') bg-emerald-100 @break
                                    @case('tenant_deleted') bg-red-100 @break
                                    @case('plan_changed') bg-purple-100 @break
                                    @case('estado_changed') bg-amber-100 @break
                                    @case('features_updated') bg-blue-100 @break
                                    @case('migrations_run') bg-cyan-100 @break
                                    @case('seed_run') bg-teal-100 @break
                                    @case('backup_run') bg-indigo-100 @break
                                    @case('backup_restored') bg-orange-100 @break
                                    @default bg-slate-100
                                @endswitch
                            ">
                                {{ $event->actionIcon() }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-slate-900">{{ $event->description }}</p>
                                    <time class="text-xs text-slate-400 whitespace-nowrap ml-4">
                                        {{ $event->created_at->format('d/m/Y H:i') }}
                                    </time>
                                </div>
                                <div class="flex items-center space-x-3 mt-1">
                                    <span class="text-xs text-slate-500">por {{ $event->admin_name }}</span>
                                    @if($event->ip_address)
                                        <span class="text-xs text-slate-300">{{ $event->ip_address }}</span>
                                    @endif
                                </div>
                                @if($event->metadata)
                                    <details class="mt-2">
                                        <summary class="text-xs text-slate-400 cursor-pointer hover:text-slate-600">Ver detalles</summary>
                                        <pre class="mt-1 text-[11px] bg-white p-2 rounded text-slate-600 overflow-x-auto">{{ json_encode($event->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            @if($events->hasPages())
                <div class="mt-6 pt-6 border-t border-slate-100">
                    {{ $events->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
