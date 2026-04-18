@extends('layouts.app')

@section('content')
@php
    $recommendedPlanId = $recommendedPlanId ?? null;
    $denial = $denial ?? session('plan_denial');
    $blockedFeature = session('blocked_feature') ?? ($denial['feature'] ?? null);
    $denialKind = session('plan_denial_kind') ?? ($denial['kind'] ?? null);
    $currentPlan = tenant()?->planRelation;
    $currentPlanName = $denial['current_plan'] ?? $currentPlan?->nombre ?? '—';
    $featureLabel = $denial['feature_label']
        ?? \App\Exceptions\PlanAccessDeniedException::resourceLabelForDenial($denialKind, $blockedFeature);
    $hasDenialContext = $denial !== null || $denialKind !== null;
    $pageTitle = $hasDenialContext
        ? 'Funcionalidad no disponible en tu plan'
        : 'Planes y mejoras';
    $pageSubtitle = \App\Exceptions\PlanAccessDeniedException::conversionSubtitle($denialKind, $blockedFeature);
    $kindBanner = $denialKind ? \App\Exceptions\PlanAccessDeniedException::denialKindMessage($denialKind) : '';
@endphp

<div class="max-w-6xl mx-auto px-4 py-10 sm:py-14">
    <div class="rounded-3xl border border-slate-200/90 bg-white shadow-xl shadow-slate-200/40 overflow-hidden">
        <div class="bg-gradient-to-br from-slate-50 via-white to-indigo-50/40 px-6 sm:px-10 py-10 sm:py-12 border-b border-slate-100">
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wider text-indigo-700/90">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-100/90 px-3 py-1">
                    <i class="fas fa-layer-group text-[10px] opacity-80"></i>
                    Plan y facturación
                </span>
                @if($hasDenialContext && $kindBanner !== '')
                    <span class="inline-flex items-center rounded-full bg-violet-100/90 px-3 py-1 text-violet-900">
                        {{ $kindBanner }}
                    </span>
                @endif
            </div>

            <h1 class="mt-5 text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
                {{ $pageTitle }}
            </h1>

            <p class="mt-3 text-base text-slate-600 leading-relaxed max-w-2xl">
                {{ $pageSubtitle }}
            </p>

            @if($blockedFeature)
                <p class="mt-4 text-sm sm:text-base text-slate-700 font-medium">
                    Estás intentando acceder a: <span class="text-indigo-700">{{ $featureLabel }}</span>
                </p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                <span class="inline-flex items-center gap-2 rounded-2xl bg-white/90 px-4 py-2.5 text-sm font-medium text-slate-800 shadow-sm ring-1 ring-slate-200/80">
                    <i class="fas fa-id-badge text-indigo-500"></i>
                    Plan actual: <span class="font-semibold text-slate-900">{{ $currentPlanName }}</span>
                </span>
            </div>

            @if(session('error'))
                <div class="mt-6 rounded-2xl border border-amber-200/80 bg-amber-50/90 px-4 py-3 text-sm text-amber-950">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
                   class="inline-flex justify-center items-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 hover:bg-slate-50 transition-colors">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Volver
                </a>
            </div>
        </div>

        <div class="px-6 sm:px-10 py-10 bg-slate-50/50">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-6">Elegí tu plan</h2>

            @if($plans->isEmpty())
                <p class="text-slate-600 text-sm leading-relaxed max-w-lg">
                    No hay planes publicados en este momento. Volvé a intentar más tarde o accedé a la consola principal de la plataforma.
                </p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($plans as $plan)
                        @php
                            $isCurrent = $currentPlan && (int) $plan->id === (int) $currentPlan->id;
                            $isLowerTier = $currentPlan
                                && (float) $plan->precio_mensual < (float) $currentPlan->precio_mensual;
                            $isRecommended = $recommendedPlanId !== null && (int) $plan->id === $recommendedPlanId;
                        @endphp
                        <div @class([
                            'relative flex flex-col rounded-2xl border bg-white p-6 shadow-sm transition-shadow',
                            'border-indigo-300 ring-2 ring-indigo-100/80 shadow-md' => $isRecommended,
                            'border-slate-200/90 hover:shadow-md' => ! $isRecommended,
                        ])>
                            @if($isRecommended)
                                <span class="absolute -top-3 left-4 inline-flex items-center rounded-full bg-indigo-600 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                    Recomendado
                                </span>
                            @endif

                            <div class="flex items-start justify-between gap-2 {{ $isRecommended ? 'mt-1' : '' }}">
                                <h3 class="text-lg font-semibold text-slate-900">{{ $plan->nombre }}</h3>
                                @if($isCurrent)
                                    <span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-900">Tu plan actual</span>
                                @endif
                            </div>

                            @if($plan->descripcion)
                                <p class="mt-3 text-sm text-slate-600 leading-relaxed line-clamp-4">{{ $plan->descripcion }}</p>
                            @endif

                            <div class="mt-5 flex items-baseline gap-1 text-slate-900">
                                <span class="text-2xl font-bold">${{ number_format((float) $plan->precio_mensual, 0, ',', '.') }}</span>
                                <span class="text-sm text-slate-500">/ mes</span>
                            </div>

                            <div class="mt-auto pt-6">
                                @if($isCurrent)
                                    <button type="button" disabled
                                            class="w-full cursor-not-allowed rounded-2xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-400">
                                        Tu plan actual
                                    </button>
                                @elseif($isLowerTier)
                                    <button type="button" disabled
                                            class="w-full cursor-not-allowed rounded-2xl bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-400">
                                        No disponible (plan inferior)
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('tenant.upgrade.process', $plan) }}">
                                        @csrf
                                        <button type="submit"
                                                class="w-full rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-md hover:bg-indigo-700 transition-colors">
                                            {{ $isRecommended ? 'Actualizar ahora' : 'Cambiar a este plan' }}
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
