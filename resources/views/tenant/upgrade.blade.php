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
<body class="min-h-screen antialiased bg-slate-100 text-slate-900">
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

    $idealForCopy = function ($plan) use ($plansList): string {
        $s = strtolower((string) $plan->slug);
        if (str_contains($s, 'basico') || str_contains($s, 'basic')) {
            return 'Ideal para compañías pequeñas que necesitan orden y visibilidad en la operación.';
        }
        if (str_contains($s, 'profesional') || str_contains($s, 'professional')) {
            return 'Ideal para una operación diaria más completa y equipos que coordinan varios frentes.';
        }
        if (str_contains($s, 'enterprise')) {
            return 'Ideal para compañías que requieren el sistema completo, gobierno y máxima capacidad.';
        }
        $fallbacks = [
            'Ideal para compañías que inician su transformación operativa.',
            'Ideal para equipos con operación diaria exigente.',
            'Ideal para organizaciones con altos estándares y escala.',
        ];
        $idx = $plansList->search(fn ($p) => (int) $p->id === (int) $plan->id);
        return $fallbacks[$idx % 3] ?? $fallbacks[0];
    };
@endphp

<div class="min-h-screen flex flex-col">
    <header class="pt-16 sm:pt-20 lg:pt-24 pb-14 sm:pb-16 px-5 sm:px-8">
        <div class="max-w-3xl mx-auto w-full text-center">
            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.22em] text-slate-500 mb-5">
                Elige cómo escalar tu operación
            </p>
            <h1 class="text-4xl sm:text-5xl lg:text-[3.25rem] font-bold text-slate-900 tracking-tight leading-[1.12]">
                Planes y mejoras
            </h1>
            <p class="mt-6 text-lg sm:text-xl text-slate-700 font-normal leading-[1.65] max-w-xl mx-auto">
                Elige el plan que mejor se adapta a tu compañía. Puedes cambiarlo en cualquier momento.
            </p>
            <p class="mt-4 text-sm sm:text-base text-slate-600 leading-[1.7] max-w-xl mx-auto">
                Desbloquea más módulos, más capacidad y una mejor experiencia operativa para tu compañía.
            </p>
            <div class="mt-12 sm:mt-14 mb-2 sm:mb-4 flex justify-center">
                <div class="inline-flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-5 py-3.5 shadow-sm">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-900 text-white">
                        <i class="fas fa-layer-group text-sm"></i>
                    </span>
                    <div class="text-left">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Tu plan hoy</p>
                        <p class="text-sm font-semibold text-slate-900">{{ tenant_plan_label() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 px-5 sm:px-8 pb-12">
        <div class="max-w-7xl mx-auto w-full">
            @if(session('error'))
                <div class="mb-10 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm text-amber-950 text-center max-w-xl mx-auto">
                    {{ session('error') }}
                </div>
            @endif

            @php
                $upgradeUx = $upgradeUx ?? ['panels' => [], 'footer_note' => null];
            @endphp
            @foreach($upgradeUx['panels'] ?? [] as $panel)
                @php
                    $pv = $panel['variant'] ?? 'info';
                    $panelWrap = match ($pv) {
                        'danger' => 'border-rose-200 bg-rose-50 text-rose-950',
                        'warning' => 'border-amber-200 bg-amber-50 text-amber-950',
                        default => 'border-sky-200 bg-sky-50 text-sky-950',
                    };
                    $panelIcon = $panel['icon'] ?? 'info-circle';
                @endphp
                <div class="mb-10 max-w-3xl mx-auto w-full rounded-2xl border {{ $panelWrap }} px-5 py-5 sm:px-6 sm:py-6 shadow-sm">
                    <div class="flex gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/80 border border-black/5">
                            <i class="fas fa-{{ $panelIcon }} text-lg opacity-90"></i>
                        </span>
                        <div class="min-w-0 flex-1 text-left">
                            <p class="text-sm font-semibold leading-snug">{{ $panel['title'] ?? '' }}</p>
                            <p class="mt-2 text-sm leading-relaxed opacity-95">{{ $panel['body'] ?? '' }}</p>
                            @if(!empty($panel['footnote']))
                                <p class="mt-3 text-xs leading-relaxed opacity-90">{{ $panel['footnote'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            @if($plansList->isEmpty())
                <p class="text-center text-slate-600 max-w-md mx-auto py-12 text-sm leading-relaxed">
                    No hay planes disponibles en este momento. Intenta más tarde.
                </p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-7 items-stretch justify-items-center xl:justify-items-stretch">
                    @foreach($plansList as $plan)
                        @php
                            $isCurrent = $currentPlan && (int) $plan->id === (int) $currentPlan->id;
                            $isLowerTier = $currentPlan
                                && (float) $plan->precio_mensual < (float) $currentPlan->precio_mensual;
                            $isRecommended = $recommendedPlanId !== null && (int) $plan->id === (int) $recommendedPlanId;
                            $isPopular = $popularPlan && (int) $plan->id === (int) $popularPlan->id;
                            $idealLine = $idealForCopy($plan);

                            $bullets = [];
                            if ($plan->max_users) {
                                $bullets[] = 'Hasta ' . (int) $plan->max_users . ' usuarios en el plan';
                            }
                            if ($plan->max_volunteers) {
                                $bullets[] = 'Hasta ' . (int) $plan->max_volunteers . ' voluntarios registrados';
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
                            $bullets = array_slice(array_unique($bullets), 0, 12);

                            $pitch = $plan->descripcion
                                ? \Illuminate\Support\Str::limit(strip_tags((string) $plan->descripcion), 220)
                                : null;
                        @endphp

                        <div class="w-full max-w-md xl:max-w-none">
                        <div @class([
                            'relative flex flex-col h-full min-h-[30rem] rounded-2xl border bg-white',
                            'border-slate-900/15 shadow-[0_8px_30px_-8px_rgba(15,23,42,0.12)] ring-1 ring-slate-900/[0.06]' => $isPopular,
                            'border-slate-200 shadow-sm hover:shadow-md hover:border-slate-300 transition-shadow duration-200' => ! $isPopular,
                        ])>
@if($isPopular)
    <span class="absolute top-0 left-1/2 z-20 -translate-x-1/2 -translate-y-1/2 whitespace-nowrap rounded-full bg-slate-900 px-4 py-1.5 text-[10px] font-semibold uppercase tracking-widest text-white shadow-md">
        Más popular
    </span>
@endif

                            <div class="relative flex flex-col flex-1 p-7 sm:p-8 pt-14 text-center {{ $isPopular ? 'bg-slate-50/40' : '' }} rounded-2xl">
                                <div class="rounded-xl border border-slate-200/90 bg-white px-4 py-4 mb-7 mx-auto w-full max-w-sm">
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 mb-2">Ideal para</p>
                                    <p class="text-sm text-slate-800 leading-relaxed font-medium">{{ $idealLine }}</p>
                                </div>

                                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $plan->nombre }}</h2>

                                <div class="mt-6 flex items-baseline justify-center gap-1.5 flex-wrap">
                                    <span class="text-4xl font-bold text-slate-900 tracking-tight tabular-nums">
                                        ${{ number_format((float) $plan->precio_mensual, 0, ',', '.') }}
                                    </span>
                                    <span class="text-base text-slate-500 font-medium">/mes</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-2 leading-relaxed max-w-xs mx-auto">Precio referencial en CLP · facturación según tu ciclo</p>

                                @if($pitch)
                                    <p class="mt-6 text-sm text-slate-600 leading-[1.65] max-w-sm mx-auto">
                                        {{ $pitch }}
                                    </p>
                                @endif

                                <div class="mt-9 pt-8 border-t border-slate-100 flex-1 flex flex-col text-left w-full max-w-sm mx-auto">
                                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400 mb-4 text-center">Incluye</p>
                                    <ul class="space-y-3 flex-1">
                                        @forelse($bullets as $line)
                                            <li class="flex items-start gap-3 text-sm text-slate-700 leading-relaxed">
                                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-900 text-white">
                                                    <i class="fas fa-check text-[9px]"></i>
                                                </span>
                                                <span>{{ $line }}</span>
                                            </li>
                                        @empty
                                            <li class="text-sm text-slate-500 text-center">Funciones esenciales para tu cuartel y tu equipo.</li>
                                        @endforelse
                                    </ul>
                                </div>

                                <div class="mt-10 pt-2 w-full max-w-sm mx-auto">
                                    @if($isCurrent)
                                        <button type="button" disabled
                                                class="w-full cursor-not-allowed rounded-xl bg-slate-100 px-5 py-3.5 text-sm font-semibold text-slate-400 border border-slate-200">
                                            Estás en este plan
                                        </button>
                                    @elseif($isLowerTier)
                                        <button type="button" disabled
                                                class="w-full cursor-not-allowed rounded-xl bg-slate-100 px-5 py-3.5 text-sm font-semibold text-slate-400 border border-slate-200">
                                            No disponible (plan inferior)
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('tenant.upgrade.process', ['targetPlan' => $plan->getKey()]) }}" class="w-full">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full rounded-xl bg-slate-900 px-5 py-3.5 text-sm font-semibold text-white shadow-sm shadow-slate-900/10 transition-colors duration-150 hover:bg-slate-800 hover:shadow-md hover:shadow-slate-900/15 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">
                                                {{ $isRecommended ? 'Actualizar ahora' : 'Cambiar a este plan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-16 max-w-7xl mx-auto border-t border-slate-200/80 pt-12">
                    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-8 sm:px-8 sm:py-9 shadow-sm">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 sm:gap-6 lg:gap-10 text-center sm:text-left">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-4 justify-center sm:justify-start">
                                <span class="mx-auto sm:mx-0 flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-700">
                                    <i class="fas fa-bolt text-sm"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Cambio inmediato</p>
                                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">El nuevo plan aplica en tu próxima navegación.</p>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-start gap-4 justify-center sm:justify-start sm:border-l sm:border-slate-200 sm:pl-8 lg:pl-10">
                                <span class="mx-auto sm:mx-0 flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-700">
                                    <i class="fas fa-database text-sm"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Sin perder tus datos</p>
                                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">Tu información y usuarios se mantienen intactos.</p>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-start gap-4 justify-center sm:justify-start sm:border-l sm:border-slate-200 sm:pl-8 lg:pl-10">
                                <span class="mx-auto sm:mx-0 flex h-11 w-11 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-700">
                                    <i class="fas fa-arrows-rotate text-sm"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Flexibilidad total</p>
                                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                                        @if(!empty($upgradeUx['footer_note']))
                                            Los cambios de plan aplican a tu contrato en la plataforma. Si tu cuenta está suspendida o con mora, la reactivación se coordina con GuardiAPP.
                                        @else
                                            Sube o baja de plan cuando lo necesites.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>

    <footer class="py-10 px-5 text-center border-t border-slate-200 bg-white">
        @if(!empty($upgradeUx['footer_note']))
            <p class="text-xs text-slate-600 max-w-lg mx-auto leading-relaxed font-medium mb-3">
                {{ $upgradeUx['footer_note'] }}
            </p>
        @endif
        <p class="text-xs text-slate-500 max-w-lg mx-auto leading-relaxed">
            Podés revisar los planes en cualquier momento. Los precios están en CLP.
        </p>
    </footer>
</div>
</body>
</html>
