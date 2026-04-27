<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel en Vivo')</title>
    <link rel="icon" type="image/x-icon" href="{{ branding()->favicon ?? asset('favicon.ico') }}">

    {{-- Inter font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Vite: CSS (Tailwind) + Vue entry --}}
    @vite(['resources/css/app.css', 'resources/js/guardia-live/app.js'])

    <style>
        html, body {
            background: linear-gradient(180deg, #0f172a 0%, #020617 100%);
            min-height: 100vh;
        }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
    </style>

    @stack('head')
</head>
<body class="text-slate-100 antialiased">

    @include('components.impersonation-banner')

    @php
        $currentUser = auth()->user();
        if (!$currentUser) {
            $panelRoute = 'tenant.login';
        } elseif ($currentUser->role === 'guardia') {
            $panelRoute = \Illuminate\Support\Facades\Route::has('guardia.dashboard') ? 'guardia.dashboard' : 'guardia';
        } else {
            $panelRoute = 'dashboard';
        }
    @endphp

    <div class="fixed top-0 left-0 right-0 z-50 bg-black/60 backdrop-blur-sm border-b border-white/10">
        <div class="flex items-center justify-between gap-3 px-3 sm:px-4 py-2">
            <a href="{{ route($panelRoute) }}"
               class="inline-flex items-center gap-2 text-white text-sm font-semibold hover:opacity-80 transition-opacity">
                <span aria-hidden="true">←</span>
                <span>Panel</span>
            </a>

            <div class="text-white/80 text-[11px] sm:text-xs font-medium text-center truncate">
                {{ config('app.name') }} · Modo en vivo
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="text-red-300 hover:text-red-200 text-sm font-semibold transition-colors">
                    Salir
                </button>
            </form>
        </div>
    </div>

    {{-- Initial state injected for Vue store --}}
    @isset($initialState)
    <script>
        window.__GUARDIA_LIVE_INITIAL_STATE__ = @json($initialState);
    </script>
    @endisset

    <div id="guardia-live-app" class="min-h-screen pt-12 sm:pt-14"></div>

    {{-- Include modals from dashboard for calendar, novelties, academies --}}
    @include('dashboard._modals')

    @stack('scripts')

    <script>
    // ── DEBUG GUARDIA LIVE (remover después de verificar) ──────────────
    document.addEventListener('DOMContentLoaded', function () {
        var state = window.__GUARDIA_LIVE_INITIAL_STATE__;
        console.log('[DEBUG] initialState.attendance_enabled =', state?.attendance_enabled);
        console.log('[DEBUG] initialState.bulk_update_url    =', state?.bulk_update_url);
        console.log('[DEBUG] initialState.staff.length       =', state?.staff?.length);
        console.log('[DEBUG] initialState.draft_editable     =', state?.draft_editable);
        console.log('[DEBUG] BUILD timestamp                 =', document.querySelector('script[src*="guardia"]')?.src ?? '(no match)');
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        var txt = (btn.textContent || '').trim();
        if (txt.includes('Guardar') || txt.includes('guardar')) {
            console.log('[DEBUG] Guardar clicked — btn:', btn, '| disabled:', btn.disabled);
        }
    }, true);
    // ─────────────────────────────────────────────────────────────────
    </script>
</body>
</html>
