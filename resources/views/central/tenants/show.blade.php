@extends('central.layouts.app')

@section('title', $tenant->nombre)

@section('content')
    <div class="mb-8">
        <a href="{{ route('central.tenants.index') }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center space-x-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <span>Volver a compañías</span>
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ $tenant->nombre }}</h1>
                <p class="text-slate-500 text-sm mt-1">ID: {{ $tenant->id }} &middot; DB: tenant_{{ $tenant->id }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('central.tenants.edit', $tenant) }}"
                   class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                    Editar
                </a>
                <form method="POST" action="{{ route('central.tenants.destroy', $tenant) }}"
                      onsubmit="return confirm('¿Eliminar esta compañía y TODA su base de datos? Esta acción es irreversible.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-xl hover:bg-red-100 transition">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Info --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900 mb-4">Información</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Nombre</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $tenant->nombre }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Número</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $tenant->numero ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Cuerpo</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $tenant->body?->nombre ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Plan</dt>
                    <dd>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $tenant->plan === 'enterprise' ? 'bg-purple-50 text-purple-700' : ($tenant->plan === 'profesional' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ ucfirst($tenant->plan) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Estado</dt>
                    <dd>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tenant->activo ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                            {{ $tenant->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Vencimiento</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $tenant->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Creada</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $tenant->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Technical --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-900 mb-4">Datos Técnicos</h2>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-slate-500">Base de datos</dt>
                    <dd><code class="text-xs bg-slate-100 px-2 py-1 rounded text-slate-600">tenant_{{ $tenant->id }}</code></dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500 mb-2">Dominios</dt>
                    <dd class="space-y-1">
                        @forelse($tenant->domains as $domain)
                            <div class="flex items-center space-x-2">
                                <code class="text-xs bg-slate-100 px-2 py-1 rounded text-slate-600">{{ $domain->domain }}</code>
                                <a href="http://{{ $domain->domain }}.dev-app.cl" target="_blank" class="text-xs text-blue-600 hover:underline">Abrir</a>
                            </div>
                        @empty
                            <span class="text-sm text-slate-400">Sin dominios</span>
                        @endforelse
                    </dd>
                </div>
            </dl>
        </div>
    </div>
@endsection
