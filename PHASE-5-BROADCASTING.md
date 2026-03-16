# 🚀 Fase 5: Broadcasting en Tiempo Real - Implementación

## ✅ Completado

### 1. Eventos Laravel Creados
- ✅ `BomberoStatusUpdated` - Cambios de estado de bombero
- ✅ `BomberoConfirmed` - Confirmación de asistencia
- ✅ `RefuerzoAdded` - Refuerzo agregado
- ✅ `ReplacementAssigned` - Reemplazo asignado
- ✅ `EmergenciaCreated` - Emergencia registrada
- ✅ `AseoUpdated` - Aseo actualizado

### 2. Canal de Broadcasting
- ✅ Canal: `tenant.{tenantId}.guardia.{guardiaId}`
- ✅ Multi-tenant compatible
- ✅ Autenticación por usuario

### 3. Integración en Controladores
- ✅ `AdministradorController::assignRefuerzo()` → `RefuerzoAdded`
- ✅ `AdministradorController::assignReplacement()` → `ReplacementAssigned`

---

## 🔄 Pendiente de Implementación

### Backend

#### 1. Agregar eventos en controladores restantes:

**CleaningWebController::store()**
```php
use App\Events\AseoUpdated;

// Después de guardar las asignaciones:
broadcast(new AseoUpdated($guardiaId, $assignedDate, $assignments))->toOthers();
```

**Admin\EmergencyController::store()**
```php
use App\Events\EmergenciaCreated;

// Después de crear la emergencia:
broadcast(new EmergenciaCreated($emergency, $guardiaId))->toOthers();
```

**AdministradorController (métodos de cambio de estado)**
```php
use App\Events\BomberoStatusUpdated;

// En updateStatus() y otros métodos que cambien estado:
broadcast(new BomberoStatusUpdated($bombero, $guardiaId))->toOthers();
```

**AdministradorController::confirmAttendance()**
```php
use App\Events\BomberoConfirmed;

// Después de confirmar asistencia:
broadcast(new BomberoConfirmed($bomberoId, $guardiaId))->toOthers();
```

---

### Frontend Vue

#### 1. Instalar Laravel Echo (si no está instalado)
```bash
npm install --save laravel-echo pusher-js
```

#### 2. Configurar Echo en `resources/js/app.js`
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

#### 3. Actualizar Pinia Store (`resources/js/guardia-live/stores/guardia.js`)

**Agregar estado para broadcasting:**
```javascript
const broadcastConnected = ref(false);
const echoChannel = ref(null);
```

**Función para inicializar broadcasting:**
```javascript
function initBroadcasting() {
    if (!window.Echo || !guardiaId.value || !tenantId.value) {
        console.warn('[Broadcasting] Echo not available or missing IDs');
        return;
    }

    const channelName = `tenant.${tenantId.value}.guardia.${guardiaId.value}`;
    console.log('[Broadcasting] Connecting to channel:', channelName);

    echoChannel.value = window.Echo.channel(channelName);

    // Evento: Bombero status updated
    echoChannel.value.listen('.bombero.status.updated', (event) => {
        console.log('[Broadcasting] Bombero status updated:', event);
        refreshState(); // Refrescar todo el estado
    });

    // Evento: Bombero confirmed
    echoChannel.value.listen('.bombero.confirmed', (event) => {
        console.log('[Broadcasting] Bombero confirmed:', event);
        const idx = staff.value.findIndex(s => s.id === event.bombero_id);
        if (idx !== -1) {
            staff.value[idx] = {
                ...staff.value[idx],
                confirmed_at: event.confirmed_at,
            };
        }
    });

    // Evento: Refuerzo added
    echoChannel.value.listen('.refuerzo.added', (event) => {
        console.log('[Broadcasting] Refuerzo added:', event);
        refreshState(); // Refrescar para mostrar el nuevo refuerzo
    });

    // Evento: Replacement assigned
    echoChannel.value.listen('.replacement.assigned', (event) => {
        console.log('[Broadcasting] Replacement assigned:', event);
        refreshState(); // Refrescar para mostrar el reemplazo
    });

    // Evento: Emergencia created
    echoChannel.value.listen('.emergencia.created', (event) => {
        console.log('[Broadcasting] Emergencia created:', event);
        refreshState(); // Refrescar para mostrar la emergencia
    });

    // Evento: Aseo updated
    echoChannel.value.listen('.aseo.updated', (event) => {
        console.log('[Broadcasting] Aseo updated:', event);
        refreshState(); // Refrescar para mostrar las asignaciones de aseo
    });

    broadcastConnected.value = true;
    console.log('[Broadcasting] Connected successfully');
}
```

**Función para desconectar broadcasting:**
```javascript
function disconnectBroadcasting() {
    if (echoChannel.value) {
        window.Echo.leave(echoChannel.value.name);
        echoChannel.value = null;
        broadcastConnected.value = false;
        console.log('[Broadcasting] Disconnected');
    }
}
```

**Modificar startPolling() para incluir fallback:**
```javascript
function startPolling() {
    if (pollingInterval.value) return;

    // Intentar conectar broadcasting primero
    initBroadcasting();

    // Mantener polling como fallback (aumentar intervalo si broadcasting está conectado)
    const interval = broadcastConnected.value ? 60000 : 30000; // 60s si broadcasting, 30s si no
    
    pollingInterval.value = setInterval(async () => {
        if (!isPolling.value) {
            isPolling.value = true;
            await refreshState();
            isPolling.value = false;
        }
    }, interval);

    console.log(`[Polling] Started with ${interval}ms interval (broadcasting: ${broadcastConnected.value})`);
}
```

**Modificar stopPolling() para desconectar broadcasting:**
```javascript
function stopPolling() {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
        console.log('[Polling] Stopped');
    }
    disconnectBroadcasting();
}
```

**Exportar nuevos estados:**
```javascript
return {
    // ... estados existentes
    broadcastConnected: computed(() => broadcastConnected.value),
    // ... acciones existentes
    initBroadcasting,
    disconnectBroadcasting,
};
```

---

## 🧪 Testing

### 1. Verificar Reverb está corriendo
```bash
php artisan reverb:start
```

### 2. Verificar variables de entorno (.env)
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 3. Testing en navegador

**Abrir consola (F12) y verificar:**
```
[Broadcasting] Connecting to channel: tenant.cuarta-temuco.guardia.2
[Broadcasting] Connected successfully
```

**Probar eventos:**
1. Abrir 2 pestañas del dashboard live
2. En pestaña 1: Agregar refuerzo
3. En pestaña 2: Debería actualizarse automáticamente sin esperar polling

**Verificar fallback:**
1. Detener Reverb: `Ctrl+C` en terminal de Reverb
2. El polling debería continuar funcionando cada 30s
3. Reiniciar Reverb
4. Broadcasting debería reconectarse automáticamente

---

## 📝 Notas Importantes

### Multi-Tenant
- ✅ Todos los eventos incluyen `tenantId` en el canal
- ✅ Verificación de tenant en `routes/channels.php`
- ✅ Solo usuarios autenticados del mismo tenant pueden escuchar

### Performance
- Broadcasting reduce carga del servidor (menos polling)
- Fallback a polling si WebSocket falla
- Polling interval aumenta a 60s cuando broadcasting está activo

### Debugging
- Todos los eventos logean en consola del navegador
- Reverb muestra conexiones en terminal
- Laravel logs en `storage/logs/laravel.log`

---

## 🚀 Deploy a Staging

### 1. Backend
```bash
git add -A
git commit -m "Phase 5: Add real-time broadcasting with Laravel Reverb"
git push
```

### 2. Servidor Staging
```bash
git pull
composer install
php artisan optimize:clear
npm install
npm run build
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### 3. Configurar supervisor para Reverb (producción)
```ini
[program:reverb]
command=php /path/to/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/path/to/project
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/reverb.log
```

---

**Última actualización:** 16 Mar 2026 12:15 PM
**Estado:** Eventos creados ✅ | Controladores parcialmente integrados 🔄 | Frontend pendiente ⏳
