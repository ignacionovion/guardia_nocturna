<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ branding()->nombre_empresa }}</title>
    <link rel="icon" type="image/x-icon" href="{{ branding()->favicon ?? asset('favicon.ico') }}">
    <style>
        :root {
            --brand-primary: {{ branding()->color_primario }};
            --brand-secondary: {{ branding()->color_secundario }};
            --brand-sidebar: {{ branding()->color_sidebar }};
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @if(in_array(Auth::user()->role ?? '', ['capitan', 'super_admin', 'capitania'], true))
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
    <script>
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env('REVERB_APP_KEY') }}',
            cluster: 'mt1',
            wsHost: '{{ env('VITE_REVERB_HOST', env('REVERB_HOST')) }}',
            wsPort: {{ env('VITE_REVERB_PORT', 443) }},
            wssPort: {{ env('VITE_REVERB_PORT', 443) }},
            forceTLS: {{ env('VITE_REVERB_SCHEME', 'https') === 'https' ? 'true' : 'false' }},
            enabledTransports: ['wss', 'ws'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            }
        });
    </script>
    @endif
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#cfd8e3] text-[#1e293b] antialiased min-h-screen flex flex-col">

    @include('components.impersonation-banner')

    @if(Auth::check() && !(Auth::user()->role === 'guardia' && request()->routeIs('dashboard')))
    <header class="h-16 bg-[#c7d2de] border-b border-[#9fb0c3] sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto px-6 h-full">
            <div class="flex justify-between items-center h-full">
                <!-- Logo / Marca -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    @if(branding()->logo)
                        <img src="{{ branding()->logo }}" alt="{{ branding()->nombre_empresa }}" class="h-8 w-auto">
                    @elseif(file_exists(public_path('brand/guardiapp.png')))
                        <img src="{{ asset('brand/guardiapp.png') }}?v={{ filemtime(public_path('brand/guardiapp.png')) }}" alt="GuardiAPP" class="h-8 w-auto">
                    @else
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-helmet-safety text-white text-sm"></i>
                            </div>
                            <span class="text-[#1e293b] font-semibold text-base hidden sm:block">{{ branding()->nombre_empresa }}</span>
                        </div>
                    @endif
                </a>

                <!-- Menú de Navegación (Desktop) -->
                <div class="hidden md:flex items-center gap-1">
                    @auth
                    @if(Auth::user()->role === 'guardia')
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3] hover:text-[#1e293b]' }}">
                            Inicio
                        </a>
                        <a href="{{ route('camas') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('camas') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3] hover:text-[#1e293b]' }}">
                            Camas
                        </a>
                    @elseif(in_array(Auth::user()->role, ['capitan', 'super_admin', 'capitania'], true))
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3] hover:text-[#1e293b]' }}">
                            Inicio
                        </a>

                        <div class="relative group">
                            <button type="button" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors text-[#1e293b] hover:bg-[#b7c4d3] flex items-center gap-1">
                                Gestión
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="hidden group-hover:block absolute left-0 top-full w-56 bg-[#dde6ef] rounded-lg shadow-lg border border-[#9fb0c3] overflow-hidden mt-1">
                                <div class="py-1"></div>
                                @if(feature('voluntarios'))
                                <a href="{{ route('admin.volunteers.index') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                    Voluntarios
                                </a>
                                @endif
                                @if(feature('emergencias'))
                                <a href="{{ route('admin.emergencies.index') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                    Emergencias
                                </a>
                                @endif
                                @if(feature('dotaciones'))
                                <a href="{{ route('admin.dotaciones') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                    Dotaciones
                                </a>
                                @endif
                                @if(feature('calendario'))
                                <a href="{{ route('admin.calendario') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                    Calendario
                                </a>
                                @endif
                            </div>
                        </div>

                        <div class="relative group">
                            <button type="button" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors text-[#1e293b] hover:bg-[#b7c4d3] flex items-center gap-1">
                                Guardias
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="hidden group-hover:block absolute left-0 top-full w-56 bg-[#dde6ef] rounded-lg shadow-lg border border-[#9fb0c3] overflow-hidden mt-1">
                                <div class="py-1"></div>
                                @if(feature('now'))
                                <a href="{{ route('guardia.now') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                    Now
                                </a>
                                @endif
                                @if(feature('guardia'))
                                <a href="{{ route('admin.guardias') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                    Guardias
                                </a>
                                @endif
                                @if(feature('camas'))
                                <a href="{{ route('camas') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                    Camas
                                </a>
                                @endif
                                @if(feature('reportes'))
                                <a href="{{ route('admin.reports.index') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                    Reportes
                                </a>
                                @endif
                            </div>
                        </div>

                        @if(feature('preventiva'))
                        <div class="relative group">
                            <button type="button" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.preventivas*') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3]' }} flex items-center gap-1">
                                Preventivas
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="hidden group-hover:block absolute left-0 top-full w-56 bg-[#dde6ef] rounded-lg shadow-lg border border-[#9fb0c3] overflow-hidden mt-1">
                                <div class="py-1"></div>
                                <a href="{{ route('admin.preventivas.index') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                    Eventos
                                </a>
                                <div class="px-4 py-2 text-xs text-[#475569] border-t border-[#9fb0c3]">Ver reportes desde el detalle de cada evento</div>
                            </div>
                        </div>
                        @endif
                        @if(feature('planilla'))
                        <a href="{{ route('admin.planillas.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.planillas*') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3]' }}">
                            Planillas
                        </a>
                        @endif
                        @if(feature('inventario'))
                        <a href="{{ route('inventario.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('inventario.*') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3]' }}">
                            Inventario
                        </a>
                        @endif
                    @endif
                    @endauth
                </div>

                <!-- Controles Mobile + Perfil -->
                <div class="flex items-center gap-3">
                    <!-- Botón Menú (Mobile) -->
                    <button type="button" id="mobile-menu-button" class="md:hidden p-2 text-[#1e293b] hover:text-[#1e293b] hover:bg-[#b7c4d3] rounded-lg transition-colors" aria-label="Abrir menú">
                        <i class="fas fa-bars"></i>
                    </button>

                    @auth
                        @if(in_array(Auth::user()->role, ['capitan', 'super_admin', 'capitania'], true))
                            <!-- Campana de Notificaciones -->
                            <div class="relative" id="notification-bell-root">
                                <button type="button" id="notification-bell-btn" class="relative p-2 text-[#1e293b] hover:text-[#1e293b] hover:bg-[#b7c4d3] rounded-lg transition-colors" title="Notificaciones">
                                    <i class="fas fa-bell"></i>
                                    <span id="notification-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center hidden">0</span>
                                </button>
                                
                                <!-- Dropdown de Notificaciones -->
                                <div id="notification-dropdown" class="hidden absolute right-0 top-full w-80 sm:w-96 mt-2 bg-[#dde6ef] rounded-lg shadow-lg border border-[#9fb0c3] overflow-hidden z-50">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-[#9fb0c3]">
                                        <span class="text-sm font-semibold text-[#1e293b]">Notificaciones</span>
                                        <button type="button" id="mark-all-read" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                            Marcar todas
                                        </button>
                                    </div>
                                    <div id="notification-list" class="max-h-80 overflow-y-auto">
                                        <div class="p-4 text-center text-sm text-slate-500">
                                            <i class="fas fa-spinner fa-spin mr-2"></i> Cargando...
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                                (function() {
                                    const bellBtn = document.getElementById('notification-bell-btn');
                                    const dropdown = document.getElementById('notification-dropdown');
                                    const badge = document.getElementById('notification-badge');
                                    const list = document.getElementById('notification-list');
                                    const markAllBtn = document.getElementById('mark-all-read');
                                    let notifications = [];

                                    function toggleDropdown() {
                                        dropdown.classList.toggle('hidden');
                                        if (!dropdown.classList.contains('hidden')) {
                                            loadNotifications();
                                        }
                                    }

                                    function closeDropdown(e) {
                                        if (!e.target.closest('#notification-bell-root')) {
                                            dropdown.classList.add('hidden');
                                        }
                                    }

                                    async function loadNotifications() {
                                        try {
                                            const response = await fetch('{{ route('notifications.index') }}');
                                            const data = await response.json();
                                            notifications = data.notifications || [];
                                            updateBadge(data.unread_count || 0);
                                            renderNotifications();
                                        } catch (error) {
                                            list.innerHTML = '<div class="p-4 text-center text-sm text-slate-500">Error al cargar notificaciones</div>';
                                        }
                                    }

                                    function updateBadge(count) {
                                        badge.dataset.count = count;
                                        if (count > 0) {
                                            badge.textContent = count > 99 ? '99+' : count;
                                            badge.classList.remove('hidden');
                                            // Animación cuando llega nueva notificación
                                            badge.classList.add('animate-bounce');
                                            setTimeout(() => badge.classList.remove('animate-bounce'), 1000);
                                        } else {
                                            badge.classList.add('hidden');
                                        }
                                    }

                                    function renderNotifications() {
                                        if (notifications.length === 0) {
                                            list.innerHTML = '<div class="p-6 text-center text-sm text-slate-500"><i class="fas fa-bell-slash text-2xl mb-2 text-slate-300"></i><br>No hay notificaciones</div>';
                                            return;
                                        }

                                        const typeIcons = {
                                            'attendance_saved': 'fa-clipboard-check text-emerald-500',
                                            'replacement': 'fa-people-arrows text-amber-500',
                                            'refuerzo': 'fa-user-plus text-blue-500',
                                            'novelty': 'fa-exclamation-circle text-purple-500',
                                            'bed_assigned': 'fa-bed text-indigo-500',
                                            'emergency': 'fa-truck-medical text-red-500',
                                            'status_changed': 'fa-user-clock text-orange-500',
                                            'inventory_movement': 'fa-boxes text-cyan-500',
                                            'form_completed': 'fa-file-lines text-teal-500',
                                            'preventive': 'fa-clipboard-list text-pink-500',
                                        };

                                        list.innerHTML = notifications.map(n => {
                                            const iconClass = typeIcons[n.type] || 'fa-bell text-slate-500';
                                            const timeAgo = new Date(n.created_at).toLocaleString('es-CL', { 
                                                day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' 
                                            });
                                            const unreadClass = !n.read ? 'bg-blue-50/50' : '';
                                            
                                            return `
                                                <div class="px-4 py-3 border-b border-slate-100 hover:bg-slate-50 transition-colors ${unreadClass}" data-id="${n.id}">
                                                    <div class="flex items-start gap-3">
                                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                                                            <i class="fas ${iconClass}"></i>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-semibold text-slate-800 truncate">${n.title}</p>
                                                            ${n.message ? `<p class="text-xs text-slate-600 mt-0.5">${n.message}</p>` : ''}
                                                            <p class="text-[10px] text-slate-400 mt-1">${timeAgo}</p>
                                                        </div>
                                                        ${!n.read ? `<button type="button" class="mark-read-btn text-blue-500 hover:text-blue-700 text-xs" data-id="${n.id}"><i class="fas fa-check"></i></button>` : ''}
                                                    </div>
                                                </div>
                                            `;
                                        }).join('');

                                        // Agregar event listeners a los botones de marcar como leída
                                        list.querySelectorAll('.mark-read-btn').forEach(btn => {
                                            btn.addEventListener('click', (e) => {
                                                e.stopPropagation();
                                                markAsRead(btn.dataset.id);
                                            });
                                        });
                                    }

                                    async function markAsRead(id) {
                                        try {
                                            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                            await fetch(`{{ url('/api/notifications') }}/${id}/read`, {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': token,
                                                    'Accept': 'application/json'
                                                }
                                            });
                                            loadNotifications();
                                        } catch (error) {
                                            console.error('Error marking as read:', error);
                                        }
                                    }

                                    async function markAllAsRead() {
                                        try {
                                            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                            await fetch('{{ route('notifications.mark_all_read') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': token,
                                                    'Accept': 'application/json'
                                                }
                                            });
                                            loadNotifications();
                                        } catch (error) {
                                            console.error('Error marking all as read:', error);
                                        }
                                    }

                                    bellBtn.addEventListener('click', toggleDropdown);
                                    markAllBtn.addEventListener('click', markAllAsRead);
                                    document.addEventListener('click', closeDropdown);

                                    // Cargar contador inicial y luego arrancar todo
                                    let lastUnreadCount = -1; // -1 = no inicializado todavía

                                    function handleNewNotificationsFromApi(newCount, prevCount) {
                                        if (prevCount < 0) return; // primera carga, no mostrar toasts
                                        if (newCount <= prevCount) return;
                                        // Hay nuevas - obtenerlas y mostrar toast
                                        fetch('{{ route('notifications.index') }}')
                                            .then(r => r.json())
                                            .then(d => {
                                                const allNotifs = d.notifications || [];
                                                const diff = newCount - prevCount;
                                                // Las diff primeras son las nuevas (vienen ordenadas desc)
                                                for (let i = diff - 1; i >= 0; i--) {
                                                    if (allNotifs[i] && !allNotifs[i].read) {
                                                        showNotificationToast(allNotifs[i].title, allNotifs[i].message, allNotifs[i].type);
                                                    }
                                                }
                                                notifications = allNotifs;
                                                if (!dropdown.classList.contains('hidden')) {
                                                    renderNotifications();
                                                }
                                            }).catch(() => {});
                                    }

                                    fetch('{{ route('notifications.unread_count') }}')
                                        .then(r => r.json())
                                        .then(data => {
                                            lastUnreadCount = data.unread_count || 0;
                                            updateBadge(lastUnreadCount);
                                        })
                                        .catch(() => { lastUnreadCount = 0; });

                                    @if(in_array(Auth::user()->role, ['capitan', 'super_admin', 'capitania']))
                                    // WebSocket con Laravel Echo + Reverb (tiempo real)
                                    if (typeof window.Echo !== 'undefined') {
                                        window.Echo.private('tenant.{{ tenant("id") }}.notifications')
                                            .listen('.notification.created', (e) => {
                                                const prev = lastUnreadCount;
                                                lastUnreadCount = prev + 1;
                                                updateBadge(lastUnreadCount);
                                                showNotificationToast(e.title, e.message, e.type);
                                                notifications.unshift({
                                                    id: e.id,
                                                    type: e.type,
                                                    title: e.title,
                                                    message: e.message,
                                                    created_at: e.created_at,
                                                    read: false
                                                });
                                                if (!dropdown.classList.contains('hidden')) {
                                                    renderNotifications();
                                                }
                                            });
                                    }

                                    // Polling de respaldo cada 15s (cubre casos donde WebSocket falla)
                                    setInterval(function() {
                                        fetch('{{ route('notifications.unread_count') }}')
                                            .then(r => r.json())
                                            .then(data => {
                                                const newCount = data.unread_count || 0;
                                                handleNewNotificationsFromApi(newCount, lastUnreadCount);
                                                lastUnreadCount = newCount;
                                                updateBadge(newCount);
                                            }).catch(() => {});
                                    }, 15000);
                                    @endif

                                    function showNotificationToast(title, message, type) {
                                        const toast = document.createElement('div');
                                        const typeColors = {
                                            'emergency': { bg: 'bg-red-600', border: 'border-red-700', label: 'Emergencia' },
                                            'bed_assigned': { bg: 'bg-blue-600', border: 'border-blue-700', label: 'Cama Asignada' },
                                            'inventory_movement': { bg: 'bg-amber-600', border: 'border-amber-700', label: 'Inventario' },
                                            'replacement': { bg: 'bg-purple-600', border: 'border-purple-700', label: 'Reemplazo' },
                                            'refuerzo': { bg: 'bg-cyan-600', border: 'border-cyan-700', label: 'Refuerzo' },
                                            'attendance_saved': { bg: 'bg-emerald-600', border: 'border-emerald-700', label: 'Asistencia' },
                                            'novelty': { bg: 'bg-indigo-600', border: 'border-indigo-700', label: 'Novedad' },
                                            'default': { bg: 'bg-slate-600', border: 'border-slate-700', label: 'Notificación' }
                                        };
                                        const color = typeColors[type] || typeColors.default;
                                        const timestamp = new Date().toLocaleString('es-CL', { 
                                            day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' 
                                        });
                                        
                                        toast.className = `fixed top-5 right-5 z-[9999] max-w-sm w-[calc(100vw-2.5rem)] animate-slide-in`;
                                        toast.innerHTML = `
                                            <div class="bg-white ${color.border} border-2 shadow-2xl rounded-xl overflow-hidden">
                                                <div class="flex items-start gap-3 p-4">
                                                    <div class="w-10 h-10 rounded-lg ${color.bg} text-white flex items-center justify-center shrink-0">
                                                        <i class="fas fa-bell"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-[10px] font-bold uppercase tracking-wider ${color.bg} text-white px-2 py-0.5 rounded">${color.label}</span>
                                                            <span class="text-[10px] text-slate-400">${timestamp}</span>
                                                        </div>
                                                        <div class="text-sm font-bold text-slate-800 mt-1.5 break-words leading-tight">${title}</div>
                                                        ${message ? `<div class="text-xs text-slate-600 mt-1 break-words">${message}</div>` : ''}
                                                    </div>
                                                    <button type="button" onclick="this.closest('.fixed')?.remove()" class="text-slate-400 hover:text-slate-700 transition-colors shrink-0">
                                                        <i class="fas fa-xmark"></i>
                                                    </button>
                                                </div>
                                                <div class="h-1 ${color.bg}"></div>
                                            </div>
                                        `;
                                        document.body.appendChild(toast);
                                        
                                        // Auto-remove after 6 seconds
                                        setTimeout(() => {
                                            toast.style.opacity = '0';
                                            toast.style.transition = 'opacity 0.3s ease';
                                            setTimeout(() => toast.remove(), 300);
                                        }, 6000);
                                    }
                                })();
                            </script>
                        @endif
                    @endauth

                    <!-- Perfil de Usuario -->
                    @auth
                        <div class="flex items-center gap-3 pl-3 ml-3 border-l border-[#9fb0c3]">
                            <div class="relative hidden sm:block" id="user-menu-root">
                                <button type="button" id="user-menu-button" class="flex flex-col items-end px-2 py-1 hover:bg-[#b7c4d3] rounded-lg transition-colors">
                                    <span class="text-[#1e293b] text-sm font-medium">{{ Auth::user()->name }}</span>
                                    <span class="text-[#475569] text-xs">{{ str_replace('_', ' ', Auth::user()->role) }}</span>
                                </button>
                                @if(in_array(Auth::user()->role, ['capitan', 'super_admin', 'capitania'], true))
                                    <div id="user-menu-dropdown" class="hidden absolute right-0 top-full w-64 mt-2">
                                        <div class="bg-[#dde6ef] rounded-lg shadow-lg border border-[#9fb0c3] overflow-hidden">
                                            <div class="py-1">
                                                <a href="{{ route('admin.system.index') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                                    Administración del Sistema
                                                </a>
                                                @if(addon('custom_branding'))
                                                <a href="{{ route('admin.branding.index') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                                    Marca Personalizada
                                                </a>
                                                @endif
                                                <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm font-medium text-[#1e293b] hover:bg-[#c3cfdb]">
                                                    Usuarios
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-2 text-[#475569] hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Cerrar Sesión">
                                    <i class="fas fa-right-from-bracket"></i>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <div id="mobile-menu" class="hidden md:hidden bg-[#c7d2de] border-b border-[#9fb0c3]">
        <div class="container mx-auto px-6 py-4 space-y-1">
            @auth
            @if(Auth::user()->role === 'guardia')
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3]' }}">Inicio</a>
                <a href="{{ route('camas') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('camas') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3]' }}">Camas</a>
            @elseif(in_array(Auth::user()->role, ['capitan', 'super_admin', 'capitania'], true))
                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3]' }}">Inicio</a>
                @if(feature('guardia'))
                <a href="{{ route('admin.guardias') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.guardias*') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3]' }}">Guardias</a>
                @endif
                @if(feature('camas'))
                <a href="{{ route('camas') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('camas') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3]' }}">Camas</a>
                @endif
                @if(feature('calendario'))
                <a href="{{ route('admin.calendario') }}" class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.calendario*') ? 'bg-[#9fb0c3] text-[#1e293b]' : 'text-[#1e293b] hover:bg-[#b7c4d3]' }}">Calendario</a>
                @endif
            @endif
            @endauth
        </div>
    </div>

    <script>
        document.getElementById('mobile-menu-button')?.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });

        (function () {
            const root = document.getElementById('user-menu-root');
            const btn = document.getElementById('user-menu-button');
            const dd = document.getElementById('user-menu-dropdown');
            if (!root || !btn || !dd) return;

            function close() {
                dd.classList.add('hidden');
            }

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dd.classList.toggle('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!root.contains(e.target)) close();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') close();
            });
        })();
    </script>
    @endif

    <main class="flex-1 bg-[#d9e2ec]">
        <div class="{{ Auth::check() && Auth::user()->role === 'guardia' && request()->routeIs('dashboard') ? 'w-full' : 'container mx-auto px-6 py-6' }}">
        <!-- Alertas Globales -->
        @if(session('success'))
            <div id="global-toast-success" class="fixed top-5 right-5 z-[9999] max-w-md w-[calc(100vw-2.5rem)]">
                <div class="bg-white border border-emerald-200 shadow-2xl rounded-2xl overflow-hidden">
                    <div class="flex items-start gap-3 p-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">Operación exitosa</div>
                            <div class="text-sm font-semibold text-slate-800 mt-1 break-words">{{ session('success') }}</div>
                        </div>
                        <button type="button" onclick="document.getElementById('global-toast-success')?.remove()" class="text-slate-400 hover:text-slate-700 transition-colors">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                    <div class="h-1 bg-emerald-600"></div>
                </div>
            </div>
            <script>
                setTimeout(() => {
                    const el = document.getElementById('global-toast-success');
                    if (el) el.remove();
                }, 4500);
            </script>
        @endif

        @if($errors->any())
            <div id="global-toast-error" class="fixed top-5 right-5 z-[9999] max-w-md w-[calc(100vw-2.5rem)]">
                <div class="bg-white border border-red-200 shadow-2xl rounded-2xl overflow-hidden">
                    <div class="flex items-start gap-3 p-4">
                        <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold uppercase tracking-wider text-red-700">Atención</div>
                            <ul class="text-sm font-semibold text-slate-800 mt-1 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li class="break-words">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" onclick="document.getElementById('global-toast-error')?.remove()" class="text-slate-400 hover:text-slate-700 transition-colors">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                    <div class="h-1 bg-red-600"></div>
                </div>
            </div>
        @endif

        @auth
            @php
                $inAppNotifications = \App\Models\InAppNotification::where('user_id', auth()->id())
                    ->whereNull('read_at')
                    ->latest()
                    ->take(3)
                    ->get();
            @endphp

            @if($inAppNotifications->isNotEmpty())
                <div id="inapp-toast-stack" class="fixed top-5 left-1/2 -translate-x-1/2 z-[9998] w-[calc(100vw-2.5rem)] max-w-xl space-y-3">
                    @foreach($inAppNotifications as $n)
                        <div class="bg-white border border-slate-200 shadow-2xl rounded-2xl overflow-hidden">
                            <div class="flex items-start gap-3 p-4">
                                <div class="w-10 h-10 rounded-xl {{ ($n->type === 'guardia') ? 'bg-red-600' : 'bg-indigo-600' }} text-white flex items-center justify-center shrink-0">
                                    <i class="{{ ($n->type === 'guardia') ? 'fas fa-bell' : 'fas fa-chalkboard-user' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ $n->title }}</div>
                                    @if($n->message)
                                        <div class="text-sm font-semibold text-slate-800 mt-1 break-words">{{ $n->message }}</div>
                                    @endif
                                    @if($n->action_url)
                                        <a href="{{ $n->action_url }}" class="inline-flex items-center gap-2 mt-2 text-xs font-bold uppercase tracking-wider text-blue-600 hover:text-blue-800">
                                            Ir
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    @endif
                                </div>
                                <button type="button" class="text-slate-400 hover:text-slate-700 transition-colors" onclick="this.closest('.bg-white')?.remove()">
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>
                            <div class="h-1 {{ ($n->type === 'guardia') ? 'bg-red-600' : 'bg-indigo-600' }}"></div>
                        </div>
                    @endforeach
                </div>

                <script>
                    (function() {
                        const ids = @json($inAppNotifications->pluck('id'));
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                        fetch(@json(route('notifications.read')), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ ids })
                        }).catch(() => {});

                        setTimeout(() => {
                            const stack = document.getElementById('inapp-toast-stack');
                            if (stack) stack.remove();
                        }, 6500);
                    })();
                </script>
            @endif
        @endauth

            @yield('content')
        </div>
    </main>

    @if(!(Auth::check() && Auth::user()->role === 'guardia' && request()->routeIs('dashboard')))
    <footer class="bg-[#c7d2de] border-t border-[#9fb0c3] mt-auto">
        <div class="container mx-auto py-6 px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-helmet-safety text-red-600"></i>
                    <span class="font-semibold text-[#1e293b]">{{ config('app.name', 'GuardiAPP') }}</span>
                </div>
                <div class="text-sm text-[#475569]">
                    © {{ date('Y') }} GuardiAPP – Plataforma SaaS para gestión de compañías de bomberos
                </div>
            </div>
        </div>
    </footer>
    @endif

    @stack('scripts')
</body>
</html>
