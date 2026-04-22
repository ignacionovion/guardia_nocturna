<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Central') — Guardia Nocturna</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="admin-panel bg-white min-h-screen">
    @php
        $centralUser = auth('central')->user();
        $isSuperAdmin = $centralUser?->is_super_admin === true;

        $navDashboardActive = request()->routeIs('central.dashboard');

        $navOrgActive = request()->routeIs(
            'central.bodies.*',
            'central.tenants.*',
            'central.check-slug',
        );

        $navFinanceActive = request()->routeIs(
            'central.financial.*',
            'central.payments.*',
            'central.billing.*',
        );

        $navBackupsActive = request()->routeIs('central.backups.*');
        $navAuditActive = request()->routeIs('central.audit.*');
        $navAdminsActive = request()->routeIs('central.admins.*');

        $orgDefaultUrl = route('central.bodies.index');
        $financeDefaultUrl = $isSuperAdmin
            ? route('central.financial.index')
            : route('central.payments.index');

        $subnavFinanceResumenActive = request()->routeIs('central.financial.*');
        $subnavFinancePagosActive = request()->routeIs('central.payments.*');
        $subnavFinancePlanesActive = request()->routeIs('central.billing.plans.*');
        $subnavFinanceBillingActive = request()->routeIs('central.billing.*') && ! $subnavFinancePlanesActive;

        $subnavOrgBodiesActive = request()->routeIs('central.bodies.*');
        $subnavOrgTenantsActive = request()->routeIs('central.tenants.*') || request()->routeIs('central.check-slug');
    @endphp

    {{-- Barra principal + subnavegación (sticky) --}}
    <div class="sticky top-0 z-50">
        <nav class="bg-slate-900 border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 py-3 md:flex-row md:items-center md:justify-between md:py-0 md:h-16 md:gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center min-w-0 flex-1 gap-3 sm:gap-4 lg:gap-8">
                        <a href="{{ route('central.dashboard') }}" class="flex items-center shrink-0 space-x-3">
                            <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                                </svg>
                            </div>
                            <span class="text-white font-bold text-lg truncate">GuardiAPP</span>
                            <span class="text-amber-400 text-xs font-semibold bg-amber-400/10 px-2 py-0.5 rounded shrink-0">CENTRAL</span>
                        </a>

                        <div class="flex flex-wrap items-center gap-1 min-w-0 sm:flex-1">
                            <a href="{{ route('central.dashboard') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap {{ $navDashboardActive ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                                Dashboard
                            </a>
                            <a href="{{ $orgDefaultUrl }}"
                               class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap {{ $navOrgActive ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                                Organización
                            </a>
                            <a href="{{ $financeDefaultUrl }}"
                               class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap {{ $navFinanceActive ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                                Finanzas
                            </a>
                            <a href="{{ route('central.backups.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap {{ $navBackupsActive ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                                Backups
                            </a>
                            <a href="{{ route('central.audit.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap {{ $navAuditActive ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                                Auditoría
                            </a>
                            @if($isSuperAdmin)
                                <a href="{{ route('central.admins.index') }}"
                                   class="px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap {{ $navAdminsActive ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                                    Administradores
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center shrink-0 space-x-4">
                        <span class="text-slate-400 text-sm hidden sm:inline text-right">
                            <span class="text-slate-500">{{ Auth::guard('central')->user()->username }}</span>
                            <span class="text-slate-600">·</span>
                            {{ Auth::guard('central')->user()->name }}
                        </span>
                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf
                            <button type="submit" class="text-slate-400 hover:text-white text-sm transition">Salir</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        @if($navOrgActive)
            <div class="bg-slate-800 border-b border-slate-700/80">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-wrap items-center gap-1 py-2">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 px-2 hidden sm:inline">Organización</span>
                        <a href="{{ route('central.bodies.index') }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap {{ $subnavOrgBodiesActive ? 'bg-slate-900 text-white' : 'text-slate-300 hover:bg-slate-900/60 hover:text-white' }} transition">
                            Cuerpos
                        </a>
                        <a href="{{ route('central.tenants.index') }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap {{ $subnavOrgTenantsActive ? 'bg-slate-900 text-white' : 'text-slate-300 hover:bg-slate-900/60 hover:text-white' }} transition">
                            Compañías
                        </a>
                    </div>
                </div>
            </div>
        @elseif($navFinanceActive)
            <div class="bg-slate-800 border-b border-slate-700/80">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-wrap items-center gap-1 py-2">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-500 px-2 hidden sm:inline">Finanzas</span>
                        @if($isSuperAdmin)
                            <a href="{{ route('central.financial.index') }}"
                               class="px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap {{ $subnavFinanceResumenActive ? 'bg-slate-900 text-white' : 'text-slate-300 hover:bg-slate-900/60 hover:text-white' }} transition">
                                Resumen financiero
                            </a>
                        @endif
                        <a href="{{ route('central.payments.index') }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap {{ $subnavFinancePagosActive ? 'bg-slate-900 text-white' : 'text-slate-300 hover:bg-slate-900/60 hover:text-white' }} transition">
                            Pagos
                        </a>
                        <a href="{{ route('central.billing.index') }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap {{ $subnavFinanceBillingActive ? 'bg-slate-900 text-white' : 'text-slate-300 hover:bg-slate-900/60 hover:text-white' }} transition">
                            Facturación
                        </a>
                        <a href="{{ route('central.billing.plans.index') }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium whitespace-nowrap {{ $subnavFinancePlanesActive ? 'bg-slate-900 text-white' : 'text-slate-300 hover:bg-slate-900/60 hover:text-white' }} transition">
                            Planes
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Flash messages --}}
    @php
        $flashSuccess = session('success');
        $flashError = session('error');
    @endphp
    @if($flashSuccess)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                <span class="whitespace-pre-line">{{ $flashSuccess }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        </div>
    @endif

    @if($flashError)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm flex items-center justify-between">
                <span class="whitespace-pre-line">{{ $flashError }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">&times;</button>
            </div>
        </div>
    @endif

    {{-- Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
