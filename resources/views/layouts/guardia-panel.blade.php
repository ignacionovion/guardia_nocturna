<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel de Control - {{ $myGuardia->name ?? 'Guardia' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ branding()->favicon ?? asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    {{-- Inter Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    {{-- Design System --}}
    @include('components.design-system')
    
    <style>
        body { 
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(180deg, #0f172a 0%, #020617 100%);
            min-height: 100vh;
        }
        
        /* Scrollbar styling for dark theme */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        /* Live indicator pulse */
        @keyframes live-pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            50% { opacity: 0.8; box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        }
        .live-indicator {
            animation: live-pulse 2s ease-in-out infinite;
        }
        
        /* Card hover effect */
        .card-hover {
            transition: all 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.4);
        }
        
        /* Gradient backgrounds */
        .bg-gradient-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e1b4b 100%);
        }
        
        /* Icon button style */
        .icon-btn {
            @apply w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200;
            @apply bg-slate-800/80 border border-slate-700/50 hover:bg-slate-700 hover:border-slate-600;
        }
        
        /* Status badge glow */
        .badge-glow-success {
            box-shadow: 0 0 12px rgba(34, 197, 94, 0.3);
        }
        .badge-glow-warning {
            box-shadow: 0 0 12px rgba(251, 191, 36, 0.3);
        }
        .badge-glow-danger {
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.3);
        }
    </style>
    
    @stack('styles')
</head>
<body class="text-slate-100 antialiased">
    @include('components.impersonation-banner')
    
    <div class="min-h-screen">
        @yield('content')
    </div>
    
    @stack('scripts')
</body>
</html>
