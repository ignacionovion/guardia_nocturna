@extends('layouts.app')

@section('content')
@php
    $denial = $denial ?? session('plan_denial');
    $currentPlanName = $denial['current_plan'] ?? optional(tenant()?->planRelation)->nombre ?? '—';
    $featureLabel = $denial['feature_label'] ?? \App\Exceptions\PlanAccessDeniedException::featureLabel($denial['feature'] ?? null);
@endphp

<div class="max-w-5xl mx-auto px-4 py-10">
    <div class="rounded-2xl border border-slate-200/80 bg-white/90 shadow-lg shadow-slate-200/50 overflow-hidden">
        <div class="bg-gradient-to-br from-indigo-50 via-white to-violet-50 px-8 py-10 border-b border-slate-100">
            <div class="inline-flex items-center gap-2 rounded-full bg-indigo-100/80 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-800">
                <i class="fas fa-layer-group"></i>
                Plan y facturación
            </div>
            <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                {{ $denial['message'] ?? 'Planes y mejoras' }}
            </h1>
            <p class="mt-3 text-slate-600 text-base leading-relaxed max-w-2xl">
                @if($denial)
                    Elegí un plan superior para desbloquear la función o ampliar límites. El cambio se aplica a tu facturación y a tu cuenta al instante.
                @else
                    Compará los planes y cambiá el tuyo cuando lo necesites. El monto se recalcula según tu ciclo de facturación (mensual o anual).
                @endif
            </p>
            @if(session('error'))
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif
            <div class="mt-6 flex flex-wrap gap-3">
                <span class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-slate-800 shadow-sm ring-1 ring-slate-200/80">
                    <i class="fas fa-puzzle-piece text-indigo-500"></i>
                    Módulo: <span class="font-semibold text-slate-900">{{ $featureLabel }}</span>
                </span>
                <span class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-slate-800 shadow-sm ring-1 ring-slate-200/80">
                    <i class="fas fa-id-badge text-violet-500"></i>
                    Plan actual: <span class="font-semibold text-slate-900">{{ $currentPlanName }}</span>
                </span>
            </div>
            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="{{ \App\Support\CentralUrls::billingPlans() }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex justify-center items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-md hover:bg-slate-800 transition-colors">
                    <i class="fas fa-external-link-alt text-xs opacity-90"></i>
                    Ver planes
                </a>
                <a href="{{ \App\Support\CentralUrls::billingIndex() }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-md hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-arrow-up-right-from-square text-xs opacity-90"></i>
                    Actualizar ahora
                </a>
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
                   class="inline-flex justify-center items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 transition-colors">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Volver
                </a>
            </div>
        </div>

        <div class="px-8 py-8 bg-slate-50/80">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Planes disponibles</h2>
            @if($plans->isEmpty())
                <p class="text-slate-600 text-sm">No hay planes publicados en este momento. Contactá al administrador de la plataforma.</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($plans as $plan)
                        @php
                            $isCurrent = (int) $plan->id === (int) optional(tenant()?->planRelation)->id;
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow ring-1 ring-transparent hover:ring-indigo-100 flex flex-col">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-slate-900">{{ $plan->nombre }}</h3>
                                @if($isCurrent)
                                    <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Tu plan actual</span>
                                @endif
                            </div>
                            @if($plan->descripcion)
                                <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $plan->descripcion }}</p>
                            @endif
                            <div class="mt-4 flex items-baseline gap-1 text-slate-900">
                                <span class="text-lg font-bold">${{ number_format((float) $plan->precio_mensual, 0, ',', '.') }}</span>
                                <span class="text-sm text-slate-500">/ mes</span>
                            </div>
                            <div class="mt-auto pt-4 border-t border-slate-100">
                                @if($isCurrent)
                                    <button type="button" disabled
                                            class="w-full rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-400 cursor-not-allowed">
                                        Plan actual
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('tenant.upgrade.process', $plan) }}">
                                        @csrf
                                        <button type="submit"
                                                class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                                            Cambiar a este plan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
