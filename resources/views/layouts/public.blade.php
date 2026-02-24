<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Guardia Nocturna')</title>
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        slate: {
                            950: '#020617',
                            900: '#0f172a',
                            800: '#1e293b',
                            700: '#334155',
                        },
                        cyan: {
                            400: '#22d3ee',
                            500: '#06b6d4',
                            600: '#0891b2',
                        },
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Animaciones */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 antialiased">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 animate-fade-in">
            <div class="bg-green-500/90 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 backdrop-blur-sm">
                <i class="fas fa-check-circle"></i>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 animate-fade-in">
            <div class="bg-amber-500/90 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 backdrop-blur-sm">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="font-semibold">{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 animate-fade-in">
            <div class="bg-red-500/90 text-white px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 backdrop-blur-sm">
                <i class="fas fa-times-circle"></i>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- Contenido Principal --}}
    <main>
        @yield('content')
    </main>

    {{-- Scripts --}}
    <script>
        // Auto-hide flash messages
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.fixed.z-50');
            flashMessages.forEach(msg => {
                setTimeout(() => {
                    msg.style.opacity = '0';
                    msg.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => msg.remove(), 500);
                }, 4000);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
