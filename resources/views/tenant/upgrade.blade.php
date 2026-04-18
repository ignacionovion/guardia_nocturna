<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Planes y mejoras — {{ branding()->nombre_empresa }}</title>
    <link rel="icon" type="image/x-icon" href="{{ branding()->favicon ?? asset('favicon.ico') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen antialiased bg-[#f1f5f9] text-slate-900">
@php
    use App\Models\Plan;
    use App\Services\PlanService;

    $recommendedPlanId = $recommendedPlanId ?? null;
    $currentPlan = tenant() ? PlanService::planForTenant(tenant()) : null;

    $plansList = $plans ?? collect();
    $popularPlan = $plansList->first(fn ($p) => strcasecmp((string) $p->slug, 'profesional') === 0)
        ?? ($plansList->count() >= 2 ? $plansList->values()->get(1) : null);

    $moduleLabels = Plan::availableModules();
    $addonLabels = Plan::availableAddons();
@endphp

<div class="min-h-screen flex flex-col">
    <header class="pt-10 sm:pt-16 pb-8 px-4 sm:px-6">
        <div class="max-w-6xl mx-auto w-full">
            <div class="flex items-center gap-3 mb-10 sm:mb-12">
                @if(branding()->logo)
                    <img src="{{ branding()->logo }}" alt="" class="h-9 w-auto">
                @elseif(file_exists(public_path('brand/guardiapp.png')))
                    <img src="{{ asset('brand/guardiapp.png') }}" alt="GuardiAPP" class="h-9 w-auto">
                @else
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center shadow-md">
                        <i class="fas fa-helmet-safety text-white text-sm"></i>
                    </div>
                @endif
                <span class="text-lg font-bold tracking-tight text-slate-900">{{ branding()->nombre_empresa }}</span>
            </div>

            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Planes y mejoras
                </h1>
                <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed">
                    Elige el plan que mejor se adapta a tu compañía. Puedes cambiarlo en cualquier momento.
                </p>
                <div class="mt-6 inline-flex items-center gap-2 rounded-full bg-sky-50 border border-sky-200/80 px-4 py-2 text-sm font-medium text-sky-900 shadow-sm">
                    <i class="fas fa-circle-check text-sky-600"></i>
                    Plan actual: {{ tenant_plan_label() }}
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 px-4 sm:px-6 pb-16">
        <div class="max-w-6xl mx-auto w-full">
            @if(session('error'))
                <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 text-center max-w-2xl mx-auto">
                    {{ session('error') }}
                </div>
            @endif

            @if($plansList->isEmpty())
                <p class="text-center text-slate-600 max-w-md mx-auto">
                    No hay planes disponibles en este momento. Intenta más tarde.
                </p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8 items-start">
                    @foreach($plansList as $plan)
                        @php
                            $isCurrent = $currentPlan && (int) $plan->id === (int) $currentPlan->id;
                            $isLowerTier = $currentPlan
                                && (float) $plan->precio_mensual < (float) $currentPlan->precio_mensual;
                            $isRecommended = $recommendedPlanId !== null && (int) $plan->id === (int) $recommendedPlanId;
                            $isPopular = $popularPlan && (int) $plan->id === (int) $popularPlan->id;

                            $bullets = [];
                            if ($plan->max_users) {
                                $bullets[] = 'Hasta ' . (int) $plan->max_users . ' usuarios';
                            }
                            foreach (array_filter($plan->features ?? []) as $key => $on) {
                                if ($on && isset($moduleLabels[$key])) {
                                    $bullets[] = $moduleLabels[$key];
                                }
                            }
                            foreach (array_filter($plan->addons ?? []) as $key => $on) {
                                if ($on && isset($addonLabels[$key])) {
                                    $bullets[] = $addonLabels[$key];
                                }
                            }
                            if ($plan->descripcion && count($bullets) < 4) {
                                $bullets[] = $plan->descripcion;
                            }
                            $bullets = array_slice(array_unique($bullets), 0, 10);
                        @endphp

                        <div @class([
                            'relative flex flex-col rounded-2xl bg-white p-6 sm:p-8 shadow-md border transition-transform duration-200',
                            'border-amber-300/90 shadow-xl shadow-amber-200/30 ring-2 ring-amber-200/50 z-10 xl:scale-[1.04]' => $isPopular,
                            'border-slate-200/90 hover:shadow-lg' => ! $isPopular,
                        ])>
                            @if($isPopular)
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-amber-400 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-amber-950 shadow-md">
                                    Más popular
                                </span>
                            @endif

                            <div class="mb-6 {{ $isPopular ? 'mt-2' : '' }}">
                                <h2 class="text-xl font-bold text-slate-900">{{ $plan->nombre }}</h2>
                                <div class="mt-4 flex items-baseline gap-1 flex-wrap">
                                    <span class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                                        ${{ number_format((float) $plan->precio_mensual, 0, ',', '.') }}
                                    </span>
                                    <span class="text-base text-slate-500 font-medium">/mes</span>
                                </div>
                            </div>

                            <ul class="space-y-3 flex-1 mb-8">
                                @forelse($bullets as $line)
                                    <li class="flex items-start gap-3 text-sm text-slate-700 leading-snug">
                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                                            <i class="fas fa-check text-[10px]"></i>
                                        </span>
                                        <span>{{ $line }}</span>
                                    </li>
                                @empty
                                    <li class="text-sm text-slate-500">Incluye las funciones esenciales de tu operación.</li>
                                @endforelse
                            </ul>

                            <div class="mt-auto pt-2">
                                @if($isCurrent)
                                    <button type="button" disabled
                                            class="w-full cursor-not-allowed rounded-2xl bg-slate-100 px-4 py-3.5 text-sm font-semibold text-slate-400 border border-slate-200">
                                        Estás en este plan
                                    </button>
                                @elseif($isLowerTier)
                                    <button type="button" disabled
                                            class="w-full cursor-not-allowed rounded-2xl bg-slate-100 px-4 py-3.5 text-sm font-semibold text-slate-400 border border-slate-200">
                                        No disponible (plan inferior)
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('tenant.upgrade.process', ['targetPlan' => $plan->getKey()]) }}" class="w-full">
                                        @csrf
                                        <button type="submit"
                                                class="w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/25 hover:from-indigo-500 hover:to-purple-500 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
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
    </main>

    <footer class="py-8 px-4 text-center">
        <p class="text-xs text-slate-500 max-w-xl mx-auto leading-relaxed">
            Puedes cambiar de plan en cualquier momento. Los precios están en CLP.
        </p>
    </footer>
</div>
</body>
</html>
