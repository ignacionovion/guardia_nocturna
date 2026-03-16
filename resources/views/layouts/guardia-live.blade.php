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

    {{-- Initial state injected for Vue store --}}
    @isset($initialState)
    <script>
        window.__GUARDIA_LIVE_INITIAL_STATE__ = @json($initialState);
    </script>
    @endisset

    <div id="guardia-live-app" class="min-h-screen"></div>

    @stack('scripts')
</body>
</html>
