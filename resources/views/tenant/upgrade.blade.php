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
<body class="min-h-screen antialiased bg-gradient-to-b from-slate-100 via-[#f4f7fb] to-slate-100 text-slate-900">
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
    <header class="relative pt-14 sm:pt-20 lg:pt-24 pb-12 sm:pb-16 px-4 sm:px-6 overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_80%_60%_at_50%_-30%,rgba(99,102,241,0.12),transparent)]"></div>
        <div class="max-w-4xl mx-auto w-full text-center relative">
            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600/90 mb-4 sm:mb-5">
                Elige cómo escalar tu operación
            </p>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-[1.08]">
                Planes y mejoras
            </h1>
            <p class="mt-5 sm:mt-6 text-lg sm:text-xl text-slate-600 font-medium leading-relaxed max-w-2xl mx-auto">
                Elige el plan que mejor se adapta a tu compañía. Puedes cambiarlo en cualquier momento.
            </p>
            <p class="mt-4 text-sm sm:text-base text-slate-500 leading-relaxed max-w-2xl mx-auto">
                Desbloquea más módulos, más capacidad y una mejor experiencia operativa para tu compañía.
            </p>
            <div class="mt-8 sm:mt-10 flex justify-center">
                <div class="inline-flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white/90 backdrop-blur-sm px-5 py-3 shadow-lg shadow-slate-200/50 ring-1 ring-white/60">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-md shadow-indigo-500/25">
                        <i class="fas fa-layer-group text-sm"></i>
                    </span>
                    <div class="text-left">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tu plan hoy</p>
                        <p class="text-sm sm:text-base font-semibold text-slate-900">{{ tenant_plan_label() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 px-4 sm:px-6 pb-8">
        <div class="max-w-6xl mx-auto w-full">
            @if(session('error'))
                <div class="mb-10 rounded-2xl border border-amber-200/90 bg-amber-50/95 px-4 py-3.5 text-sm text-amber-950 text-center max-w-2xl mx-auto shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($plansList->isEmpty())
                <p class="text-center text-slate-600 max-w-md mx-auto py-12">
                    No hay planes disponibles en este momento. Intenta más tarde.
                </p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8 xl:gap-6 items-stretch">
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

                        <div @class([
                            'relative flex flex-col h-full rounded-3xl border bg-white transition-all duration-300',
                            'border-indigo-300/90 shadow-[0_24px_48px_-12px_rgba(79,70,229,0.22)] ring-[3px] ring-indigo-100/90 z-10 xl:scale-[1.05] xl:-translate-y-1' => $isPopular,
                            'border-slate-200/90 shadow-md shadow-slate-200/40 hover:shadow-xl hover:border-slate-300/80' => ! $isPopular,
                        ])>
                            @if($isPopular)
                                <div class="absolute -inset-px rounded-3xl bg-gradient-to-b from-indigo-400/20 via-transparent to-violet-500/10 pointer-events-none"></div>
                                <span class="absolute -top-3.5 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-full bg-gradient-to-r from-amber-400 to-orange-400 px-4 py-1.5 text-[11px] font-extrabold uppercase tracking-widest text-amber-950 shadow-lg">
                                    Más popular
                                </span>
                            @endif

                            <div class="relative flex flex-col flex-1 p-6 sm:p-8 pt-8 sm:pt-10 {{ $isPopular ? 'bg-gradient-to-b from-white via-white to-indigo-50/40' : '' }} rounded-3xl">
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3 mb-6">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-600/80 mb-1">Ideal para</p>
                                    <p class="text-sm font-semibold text-slate-800 leading-snug">{{ $idealLine }}</p>
                                </div>

                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $plan->nombre }}</h2>

                                <div class="mt-5 flex items-baseline gap-1.5 flex-wrap">
                                    <span class="text-4xl sm:text-[2.75rem] font-black text-slate-900 tracking-tight tabular-nums">
                                        ${{ number_format((float) $plan->precio_mensual, 0, ',', '.') }}
                                    </span>
                                    <span class="text-lg text-slate-500 font-semibold">/mes</span>
                                </div>
                                <p class="text-xs font-medium text-slate-400 mt-1">Precio referencial en CLP · facturación según tu ciclo</p>

                                @if($pitch)
                                    <p class="mt-5 text-sm text-slate-600 leading-relaxed border-l-2 border-indigo-200 pl-4">
                                        {{ $pitch }}
                                    </p>
                                @endif

                                <div class="mt-8 pt-6 border-t border-slate-100 flex-1 flex flex-col">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-400 mb-4">Incluye</p>
                                    <ul class="space-y-3 flex-1">
                                        @forelse($bullets as $line)
                                            <li class="flex items-start gap-3 text-sm text-slate-700 leading-snug">
                                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-sm">
                                                    <i class="fas fa-check text-[9px]"></i>
                                                </span>
                                                <span>{{ $line }}</span>
                                            </li>
                                        @empty
                                            <li class="text-sm text-slate-500">Funciones esenciales para tu cuartel y tu equipo.</li>
                                        @endforelse
                                    </ul>
                                </div>

                                <div class="mt-8 pt-2">
                                    @if($isCurrent)
                                        <button type="button" disabled
                                                class="w-full cursor-not-allowed rounded-2xl bg-slate-100 px-5 py-4 text-sm font-semibold text-slate-400 border border-slate-200/80">
                                            Estás en este plan
                                        </button>
                                    @elseif($isLowerTier)
                                        <button type="button" disabled
                                                class="w-full cursor-not-allowed rounded-2xl bg-slate-100 px-5 py-4 text-sm font-semibold text-slate-400 border border-slate-200/80">
                                            No disponible (plan inferior)
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('tenant.upgrade.process', ['targetPlan' => $plan->getKey()]) }}" class="w-full">
                                            @csrf
                                            <button type="submit"
                                                    class="w-full rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-600 to-purple-600 px-5 py-4 text-base font-bold text-white shadow-xl shadow-indigo-500/30 hover:shadow-2xl hover:shadow-indigo-500/35 hover:from-indigo-500 hover:to-purple-500 active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                {{ $isRecommended ? 'Actualizar ahora' : 'Cambiar a este plan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-14 sm:mt-16 grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-5xl mx-auto">
                    <div class="flex items-start gap-3 rounded-2xl border border-slate-200/80 bg-white/90 px-4 py-4 shadow-sm">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Cambio inmediato</p>
                            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">El nuevo plan aplica en tu próxima navegación.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-2xl border border-slate-200/80 bg-white/90 px-4 py-4 shadow-sm">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <i class="fas fa-database"></i>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Sin perder tus datos</p>
                            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Tu información y usuarios se mantienen intactos.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-2xl border border-slate-200/80 bg-white/90 px-4 py-4 shadow-sm">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                            <i class="fas fa-arrows-rotate"></i>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Flexibilidad total</p>
                            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Sube o baja de plan cuando lo necesites.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </main>

    <footer class="py-10 px-4 text-center border-t border-slate-200/60 bg-white/40">
        <p class="text-xs text-slate-500 max-w-xl mx-auto leading-relaxed">
            Puedes cambiar de plan en cualquier momento. Los precios están en CLP.
        </p>
    </footer>
</div>
</body>
</html>
