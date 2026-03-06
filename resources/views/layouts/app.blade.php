<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'AppGuardia') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @if(in_array(Auth::user()->role ?? '', ['super_admin', 'capitania'], true))
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
</head>
<body class="bg-gradient-to-b from-slate-100 via-slate-50 to-slate-100 font-sans leading-normal tracking-normal flex flex-col min-h-screen text-slate-800">

    @if(!(Auth::check() && Auth::user()->role === 'guardia' && request()->routeIs('dashboard')))
    <nav class="bg-slate-900 shadow-xl border-b-4 border-red-700 sticky top-0 z-50 pt-[env(safe-area-inset-top)]">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo / Marca -->
                <a href="{{ route('dashboard') }}" class="flex items-center group">
                    @if(file_exists(public_path('brand/guardiapp.png')))
                        <img src="{{ asset('brand/guardiapp.png') }}?v={{ filemtime(public_path('brand/guardiapp.png')) }}" alt="GuardiAPP" class="h-14 w-auto drop-shadow-sm">
                    @else
                        <div class="bg-red-300 p-2.5 rounded-lg text-white transform group-hover:rotate-3 transition-transform duration-300 shadow-lg border border-red-200">
                            <i class="fas fa-helmet-safety text-xl"></i>
                        </div>
                    @endif
                </a>

                <!-- Menú de Navegación (Desktop) -->
                <div class="hidden md:flex items-center space-x-1">
                    @auth
                    @if(Auth::user()->role === 'guardia')
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-home mr-1.5 opacity-70"></i> Inicio
                        </a>
                        <a href="{{ route('camas') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('camas') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-bed mr-1.5 opacity-70"></i> Camas
                        </a>
                    @elseif(Auth::user()->role === 'inventario')
                        <a href="{{ route('inventario.index') }}" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('inventario.*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-boxes-stacked mr-1.5 opacity-80"></i> Inventario
                        </a>
                    @elseif(Auth::user()->role === 'super_admin')
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-home mr-1.5 opacity-80"></i> Inicio
                        </a>
                        <div class="h-6 w-px bg-slate-700 mx-2"></div>

                        <div class="relative group">
                            <button type="button" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors text-slate-200 hover:bg-slate-800 hover:text-white">
                                <i class="fas fa-layer-group mr-1.5 opacity-80"></i>
                                Gestión
                                <i class="fas fa-chevron-down ml-1 text-[10px] opacity-70"></i>
                            </button>
                            <div class="hidden group-hover:block absolute left-0 top-full w-56 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                <div class="h-2"></div>
                                <a href="{{ route('admin.volunteers.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-users mr-2 text-slate-500"></i> Voluntarios
                                </a>
                                <a href="{{ route('admin.emergencies.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-truck-medical mr-2 text-slate-500"></i> Emergencias
                                </a>
                                <a href="{{ route('admin.dotaciones') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-users-gear mr-2 text-slate-500"></i> Dotaciones
                                </a>
                                <a href="{{ route('admin.calendario') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-calendar-alt mr-2 text-slate-500"></i> Calendario
                                </a>
                            </div>
                        </div>

                        <div class="relative group">
                            <button type="button" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors text-slate-200 hover:bg-slate-800 hover:text-white">
                                <i class="fas fa-shield-halved mr-1.5 opacity-80"></i>
                                Guardias
                                <i class="fas fa-chevron-down ml-1 text-[10px] opacity-70"></i>
                            </button>
                            <div class="hidden group-hover:block absolute left-0 top-full w-56 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                <div class="h-2"></div>
                                <a href="{{ route('guardia.now') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-bolt mr-2 text-slate-500"></i> Now
                                </a>
                                <a href="{{ route('admin.guardias') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-shield mr-2 text-slate-500"></i> Guardias
                                </a>
                                <a href="{{ route('camas') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-bed mr-2 text-slate-500"></i> Camas
                                </a>
                                <a href="{{ route('admin.reports.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-chart-pie mr-2 text-slate-500"></i> Reportes
                                </a>
                            </div>
                        </div>

                        <div class="relative group">
                            <button type="button" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('admin.preventivas*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-clipboard-list mr-1.5 opacity-80"></i>
                                Preventivas
                                <i class="fas fa-chevron-down ml-1 text-[10px] opacity-70"></i>
                            </button>
                            <div class="hidden group-hover:block absolute left-0 top-full w-56 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                <div class="h-2"></div>
                                <a href="{{ route('admin.preventivas.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-list mr-2 text-slate-500"></i> Eventos
                                </a>
                                <div class="px-4 py-2 text-[10px] font-black text-slate-400 uppercase tracking-wider">Reportes por evento</div>
                                <div class="px-4 pb-2 text-xs text-slate-500">Ver reporte desde el detalle de cada evento</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.planillas.index') }}" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('admin.planillas*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-table-list mr-1.5 opacity-80"></i> Planillas
                        </a>
                        <a href="{{ route('inventario.index') }}" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('inventario.*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-boxes-stacked mr-1.5 opacity-80"></i> Inventario
                        </a>
                    @elseif(Auth::user()->role === 'ayudante')
                        <div class="relative group">
                            <button type="button" class="px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ request()->routeIs('admin.preventivas*') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-clipboard-list mr-1.5 opacity-80"></i>
                                Preventivas
                                <i class="fas fa-chevron-down ml-1 text-[10px] opacity-70"></i>
                            </button>
                            <div class="hidden group-hover:block absolute left-0 top-full w-56 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                <div class="h-2"></div>
                                <a href="{{ route('admin.preventivas.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-list mr-2 text-slate-500"></i> Eventos
                                </a>
                                <div class="px-4 py-2 text-[10px] font-black text-slate-400 uppercase tracking-wider">Reportes por evento</div>
                                <div class="px-4 pb-2 text-xs text-slate-500">Ver reporte desde el detalle de cada evento</div>
                            </div>
                        </div>
                    @elseif(Auth::user()->role === 'capitania')
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-home mr-1.5 opacity-70"></i> Inicio
                        </a>
                        <a href="{{ route('camas') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('camas') ? 'bg-slate-800 text-white shadow-inner' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <i class="fas fa-bed mr-1.5 opacity-70"></i> Camas
                        </a>
                    @endif
                    
                    @if(in_array(Auth::user()->role, ['super_admin', 'capitania'], true) && Auth::user()->role !== 'super_admin')
                        <div class="h-6 w-px bg-slate-700 mx-2"></div>
                        
                        <a href="{{ route('admin.guardias') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.guardias*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-shield mr-1.5 text-red-400"></i> Guardias
                        </a>
                        <a href="{{ route('admin.calendario') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.calendario*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-calendar-alt mr-1.5 text-red-400"></i> Calendario
                        </a>
                        <a href="{{ route('admin.volunteers.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.volunteers*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-users mr-1.5 text-red-400"></i> Voluntarios
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.users*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-user-shield mr-1.5 text-red-400"></i> Usuarios
                        </a>
                        <a href="{{ route('admin.emergencies.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.emergencies*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-truck-medical mr-1.5 text-red-400"></i> Emergencias
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 rounded-md text-sm font-medium transition-colors {{ request()->routeIs('admin.reports*') ? 'bg-red-900/50 text-red-100 shadow-inner' : 'text-slate-300 hover:bg-red-900/30 hover:text-red-100' }}">
                            <i class="fas fa-chart-pie mr-1.5 text-red-400"></i> Reportes
                        </a>
                    @endif
                    @endauth
                </div>

                <!-- Controles Mobile + Perfil -->
                <div class="flex items-center gap-2">
                    <!-- Botón Menú (Mobile) -->
                    <button type="button" id="mobile-menu-button" class="md:hidden shrink-0 bg-slate-800 text-slate-200 p-2 rounded-lg transition-all duration-200 border border-slate-700" aria-label="Abrir menú">
                        <i class="fas fa-bars"></i>
                    </button>

                    @auth
                        @if(in_array(Auth::user()->role, ['super_admin', 'capitania'], true))
                            <!-- Campana de Notificaciones -->
                            <div class="relative" id="notification-bell-root">
                                <button type="button" id="notification-bell-btn" class="relative p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-all duration-200 border border-slate-700" title="Notificaciones">
                                    <i class="fas fa-bell text-lg"></i>
                                    <span id="notification-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center hidden">0</span>
                                </button>
                                
                                <!-- Dropdown de Notificaciones -->
                                <div id="notification-dropdown" class="hidden absolute right-0 top-full w-80 sm:w-96 mt-2 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden z-50">
                                    <div class="flex items-center justify-between px-4 py-3 bg-slate-50 border-b border-slate-200">
                                        <span class="text-sm font-bold text-slate-800">Notificaciones</span>
                                        <button type="button" id="mark-all-read" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                            Marcar todas como leídas
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

                                    @if(in_array(Auth::user()->role, ['super_admin', 'capitania']))
                                    // WebSocket con Laravel Echo + Reverb (tiempo real)
                                    if (typeof window.Echo !== 'undefined') {
                                        window.Echo.private('notifications')
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
                        <div class="flex items-center pl-3 ml-1 border-l border-slate-700">
                            <div class="relative hidden sm:block mr-3" id="user-menu-root">
                                <button type="button" id="user-menu-button" class="flex flex-col items-end rounded-lg px-2 py-1 hover:bg-slate-800 transition-colors">
                                    <span class="text-slate-200 text-sm font-semibold leading-tight">{{ Auth::user()->name }}</span>
                                    <span class="text-slate-500 text-xs uppercase">{{ str_replace('_', ' ', Auth::user()->role) }}</span>
                                </button>
                                @if(Auth::user()->role === 'super_admin')
                                    <div id="user-menu-dropdown" class="hidden absolute right-0 top-full w-64 pt-2">
                                        <div class="bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
                                            <div class="py-2">
                                                <a href="{{ route('admin.system.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                                    <i class="fas fa-gear mr-2 text-slate-500"></i> Administración del Sistema
                                                </a>
                                                <a href="{{ route('admin.users.index') }}" class="block px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                                    <i class="fas fa-user-shield mr-2 text-slate-500"></i> Usuarios
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="bg-slate-800 hover:bg-red-900 text-slate-300 hover:text-white p-2 rounded-lg transition-all duration-200 border border-slate-700 hover:border-red-700" title="Cerrar Sesión">
                                    <i class="fas fa-right-from-bracket"></i>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Menú Mobile -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="mt-3 rounded-2xl border border-slate-800 bg-slate-950/30 overflow-hidden">
                    <div class="p-2 space-y-1">
                        @auth
                            @if(Auth::user()->role === 'guardia')
                                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-home mr-2 opacity-80"></i> Inicio
                                </a>
                                <a href="{{ route('camas') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('camas') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-bed mr-2 opacity-80"></i> Camas
                                </a>
                            @elseif(Auth::user()->role === 'inventario')
                                <a href="{{ route('inventario.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('inventario.*') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-boxes-stacked mr-2 opacity-80"></i> Inventario
                                </a>
                                <a href="{{ route('admin.volunteers.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-users mr-2 opacity-80"></i> Voluntarios
                                </a>
                                <a href="{{ route('admin.emergencies.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-truck-medical mr-2 opacity-80"></i> Emergencias
                                </a>
                            @elseif(Auth::user()->role === 'super_admin')
                                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-home mr-2 opacity-80"></i> Inicio
                                </a>
                                <a href="{{ route('guardia.now') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('guardia.now*') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-bolt mr-2 opacity-80"></i> Now
                                </a>
                                <a href="{{ route('admin.volunteers.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-users mr-2 opacity-80"></i> Voluntarios
                                </a>
                                <a href="{{ route('admin.emergencies.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-truck-medical mr-2 opacity-80"></i> Emergencias
                                </a>
                                <a href="{{ route('admin.calendario') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-calendar-alt mr-2 opacity-80"></i> Calendario
                                </a>
                                <a href="{{ route('admin.guardias') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-shield mr-2 opacity-80"></i> Guardias
                                </a>
                                <a href="{{ route('camas') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-bed mr-2 opacity-80"></i> Camas
                                </a>
                                <a href="{{ route('admin.system.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-gear mr-2 opacity-80"></i> Administración del Sistema
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-user-shield mr-2 opacity-80"></i> Usuarios
                                </a>
                                <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-chart-pie mr-2 opacity-80"></i> Reportes
                                </a>
                                <a href="{{ route('admin.preventivas.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-clipboard-list mr-2 opacity-80"></i> Preventivas
                                </a>
                                <a href="{{ route('admin.planillas.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-table-list mr-2 opacity-80"></i> Planillas
                                </a>
                                <a href="{{ route('inventario.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white">
                                    <i class="fas fa-boxes-stacked mr-2 opacity-80"></i> Inventario
                                </a>
                            @elseif(Auth::user()->role === 'capitania')
                                <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-home mr-2 opacity-80"></i> Inicio
                                </a>
                                <a href="{{ route('camas') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('camas') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-bed mr-2 opacity-80"></i> Camas
                                </a>
                            @elseif(Auth::user()->role === 'ayudante')
                                <a href="{{ route('admin.preventivas.index') }}" class="block px-3 py-2 rounded-xl text-sm font-semibold transition-colors {{ request()->routeIs('admin.preventivas*') ? 'bg-slate-800 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white' }}">
                                    <i class="fas fa-clipboard-list mr-2 opacity-80"></i> Preventivas
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <script>
        (function () {
            const btn = document.getElementById('mobile-menu-button');
            const menu = document.getElementById('mobile-menu');
            if (!btn || !menu) return;

            btn.addEventListener('click', function () {
                menu.classList.toggle('hidden');
            });
        })();

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

    <div class="{{ Auth::check() && Auth::user()->role === 'guardia' && request()->routeIs('dashboard') ? 'w-full flex-grow' : 'container mx-auto mt-0 px-4 pb-6 pt-6 flex-grow bg-slate-100/80 rounded-3xl border border-slate-200' }}">
        <!-- Alertas Globales -->
        @if(session('success'))
            <div id="global-toast-success" class="fixed top-5 right-5 z-[9999] max-w-md w-[calc(100vw-2.5rem)]">
                <div class="bg-white border border-emerald-200 shadow-2xl rounded-2xl overflow-hidden">
                    <div class="flex items-start gap-3 p-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-black uppercase tracking-widest text-emerald-700">Operación exitosa</div>
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
                            <div class="text-xs font-black uppercase tracking-widest text-red-700">Atención</div>
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
                                    <div class="text-xs font-black uppercase tracking-widest text-slate-700">{{ $n->title }}</div>
                                    @if($n->message)
                                        <div class="text-sm font-semibold text-slate-800 mt-1 break-words">{{ $n->message }}</div>
                                    @endif
                                    @if($n->action_url)
                                        <a href="{{ $n->action_url }}" class="inline-flex items-center gap-2 mt-2 text-xs font-black uppercase tracking-widest text-blue-600 hover:text-blue-800">
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

    @if(!(Auth::check() && Auth::user()->role === 'guardia' && request()->routeIs('dashboard')))
        <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 mt-auto">
            <div class="container mx-auto py-8 px-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="flex items-center space-x-2 mb-4 md:mb-0">
                        <i class="fas fa-helmet-safety text-red-700 text-xl"></i>
                        <span class="font-bold text-slate-200 tracking-wide">{{ strtoupper(config('app.name', 'AppGuardia')) }}</span>
                    </div>
                    <div class="text-sm">
                        &copy; {{ date('Y') }} Sistema de Gestión de Cuerpo de Bomberos. Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </footer>
    @endif

    @stack('scripts')
</body>
</html>
