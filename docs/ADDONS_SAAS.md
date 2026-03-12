# Addons SaaS de GuardiAPP
## Documentación Técnica y Funcional

**Fecha:** Marzo 2026  
**Versión:** 1.0  
**Sistema:** GuardiAPP - SaaS Multi-Tenant para Compañías de Bomberos

---

## Introducción

Los **Addons SaaS** son funcionalidades comerciales adicionales que extienden las capacidades del sistema más allá de los módulos operativos básicos. A diferencia de los módulos del sistema (voluntarios, dotaciones, guardias, etc.), los addons proporcionan capacidades de integración, personalización y administración avanzada dirigidas a clientes Enterprise o con necesidades específicas.

Estos addons están controlados mediante **Feature Flags** y pueden habilitarse o deshabilitarse por plan (Básico, Profesional, Enterprise) o mediante overrides específicos por tenant.

---

## 1. Acceso API REST (`api_access`)

### ¿Qué significa este addon?

Permite a terceros sistemas (aplicaciones móviles, dashboards externos, sistemas de emergencias municipales) conectarse programáticamente a GuardiAPP para consultar y modificar datos mediante endpoints HTTP RESTful con autenticación segura.

### ¿Está implementado actualmente?

**No completamente.** No existe actualmente un API REST documentada con endpoints específicos para consumo externo.

### Implementación futura sugerida

**Endpoints potenciales:**

| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/v1/volunteers` | GET | Listar voluntarios de la compañía |
| `/api/v1/guardia/{id}/schedule` | GET | Obtener programación de guardia |
| `/api/v1/emergencies` | POST | Registrar emergencia desde sistema externo |
| `/api/v1/attendance` | GET | Consultar asistencias del período |
| `/api/v1/inventory` | GET/POST | Consultar/modificar inventario |

**Técnica en Laravel:**

```php
// routes/api.php (tenant context)
Route::middleware(['auth:sanctum', 'addon:api_access'])->group(function () {
    Route::apiResource('volunteers', ApiVolunteerController::class);
    Route::get('guardia/{guardia}/roster', [ApiGuardiaController::class, 'roster']);
});
```

**Autenticación:** Sanctum para API tokens por tenant  
**Middleware:** `addon:api_access` para verificar feature flag

### ¿Dónde revisarlo?

- Archivos a crear: `routes/api.php`, `app/Http/Controllers/Api/`
- Configuración: `config/sanctum.php` (para tokens por tenant)

---

## 2. Marca Personalizada (`custom_branding`)

### ¿Qué significa este addon?

Permite a cada compañía personalizar la apariencia visual del sistema con sus propios colores institucionales, logo personalizado, y posiblemente dominio/email de remitente personalizado.

### ¿Está implementado actualmente?

**Parcialmente.** El sistema tiene la base implementada pero no está completamente integrada en todas las vistas.

### Estado actual

**Lo que YA existe:**

| Elemento | Estado | Ubicación |
|----------|--------|-----------|
| Logo del tenant | ✅ Implementado | `SystemSetting::get('tenant_logo')` |
| Nombre para mostrar | ✅ Implementado | `SystemSetting::get('tenant_display_name')` |
| Color primario/secundario | ⚠️ UI existe, no aplicado | `admin/tenant-settings.blade.php` |
| Zona horaria | ✅ Implementado | `SystemSetting::get('tenant_timezone')` |
| Email remitente | ✅ Implementado | `SystemSetting::get('tenant_email_from')` |

**Vista de configuración:**  
`@/Users/ignacionovion/Documents/Sistemas/Guardia Nocturna/resources/views/admin/tenant-settings.blade.php:113-148`

```php
@if(feature('custom_branding'))
<div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
    <h2 class="text-lg font-semibold text-slate-900 mb-4">Personalización Visual</h2>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="tenant_primary_color">Color Primario</label>
            <input type="color" id="tenant_primary_color" name="tenant_primary_color" ...>
        </div>
        <div>
            <label for="tenant_secondary_color">Color Secundario</label>
            <input type="color" id="tenant_secondary_color" name="tenant_secondary_color" ...>
        </div>
    </div>
</div>
@endif
```

### Implementación técnica recomendada

**Para aplicar colores dinámicamente:**

```php
// En un middleware o ServiceProvider
View::composer('*', function ($view) {
    if (feature('custom_branding')) {
        $view->with('primaryColor', SystemSetting::get('tenant_primary_color', '#f59e0b'));
        $view->with('secondaryColor', SystemSetting::get('tenant_secondary_color', '#1e293b'));
    }
});
```

**CSS dinámico:**
```html
<style>
    :root {
        --tenant-primary: {{ $primaryColor }};
        --tenant-secondary: {{ $secondaryColor }};
    }
</style>
```

### ¿Dónde revisarlo?

- Configuración: `/admin/configuracion` → Sección "Personalización Visual"
- Archivo: `resources/views/admin/tenant-settings.blade.php`
- Settings: `SystemSetting` con keys `tenant_primary_color`, `tenant_secondary_color`, `tenant_logo`

---

## 3. Backups Automáticos (`backup_automatico`)

### ¿Qué significa este addon?

Realiza copias de seguridad automáticas y programadas de las bases de datos de todos los tenants, con retención configurable y posibilidad de descarga/restauración manual desde el panel SaaS.

### ¿Está implementado actualmente?

**✅ Sí, completamente implementado.**

### ¿Cómo se usa actualmente?

**Comando disponible:**

```bash
# Backup de todos los tenants activos
php artisan tenant:backup

# Backup de un tenant específico
php artisan tenant:backup --tenant=tercera-temuco

# Mantener backups de los últimos 14 días
php artisan tenant:backup --keep=14
```

**Programación automática:**  
El backup está programado en `bootstrap/app.php` para ejecutarse diariamente a las 03:00:

```php
->withSchedule(function (Schedule $schedule) {
    $schedule->command('tenant:backup --keep=7')->dailyAt('03:00');
})
```

**Panel de administración:**  
`/sas/backups` (ruta: `central.backups.index`)

- Lista todos los backups con tamaño, fecha, tenant asociado
- Permite descargar archivos `.sql.gz`
- Permite restaurar backups (con autenticación requerida)
- Registra acciones en audit log

### Implementación técnica

**Archivos clave:**

| Archivo | Función |
|---------|---------|
| `app/Console/Commands/TenantBackupCommand.php` | Comando de backup con mysqldump y fallback PHP |
| `app/Http/Controllers/Central/BackupController.php` | UI para gestión de backups |
| `storage/app/backups/` | Ubicación de archivos `.sql.gz` |

**Métodos de backup:**
1. **Symfony Process** (preferido) - Usa `mysqldump` del sistema
2. **proc_open** directo - Fallback si Process falla
3. **PHP puro** - Último recurso, genera SQL manualmente

### ¿Dónde revisarlo?

- Comando: `php artisan tenant:backup --help`
- Panel SaaS: `https://sas.dev-app.cl/sas/backups`
- Archivos: `storage/app/backups/`
- Código: `@/Users/ignacionovion/Documents/Sistemas/Guardia Nocturna/app/Console/Commands/TenantBackupCommand.php`

---

## 4. Logs de Auditoría (`audit_logs`)

### ¿Qué significa este addon?

Registra todas las acciones administrativas realizadas en el panel SaaS central: creación de tenants, cambios de plan, ejecución de migraciones, restauración de backups, etc. Proporciona trazabilidad completa de quién hizo qué y cuándo.

### ¿Está implementado actualmente?

**✅ Sí, completamente implementado.**

### ¿Cómo se usa actualmente?

**Modelo:** `CentralAuditLog` con conexión a base de datos central.

**Acciones registradas:**

| Acción | Icono | Descripción |
|--------|-------|-------------|
| `tenant_created` | 🟢 | Nueva compañía creada |
| `tenant_updated` | ✏️ | Datos de compañía modificados |
| `tenant_deleted` | 🔴 | Compañía eliminada |
| `tenant_suspended` | ⏸️ | Compañía suspendida |
| `tenant_reactivated` | ▶️ | Compañía reactivada |
| `plan_changed` | 📋 | Cambio de plan (básico/pro/enterprise) |
| `features_updated` | 🔧 | Modificación de feature flags |
| `migrations_run` | 🗄️ | Migraciones ejecutadas en tenant |
| `seed_run` | 🌱 | Datos de prueba cargados |
| `backup_run` | 💾 | Backup manual o automático |
| `backup_restored` | ♻️ | Restauración de backup |
| `estado_changed` | 🔄 | Cambio de estado de tenant |

**Uso en código:**

```php
use App\Models\CentralAuditLog;

CentralAuditLog::log(
    action: 'tenant_created',
    description: 'Compañía "Tercera Temuco" creada',
    tenantId: 'tercera-temuco',
    metadata: ['plan' => 'basico', 'admin' => auth()->id()]
);
```

### Implementación técnica

**Modelo:** `@/Users/ignacionovion/Documents/Sistemas/Guardia Nocturna/app/Models/CentralAuditLog.php`

```php
class CentralAuditLog extends Model
{
    protected $connection = 'central';
    protected $fillable = [
        'admin_id', 'admin_name', 'tenant_id', 'action', 
        'description', 'metadata', 'ip_address'
    ];
    protected $casts = ['metadata' => 'array'];
}
```

**Scopes disponibles:**
- `forTenant($tenantId)` - Filtrar por compañía
- `ofAction($action)` - Filtrar por tipo de acción

### ¿Dónde revisarlo?

- Panel SaaS: Sección de logs/auditoría (si hay vista creada)
- BD Central: Tabla `central_audit_logs`
- Código: `@/Users/ignacionovion/Documents/Sistemas/Guardia Nocturna/app/Models/CentralAuditLog.php`
- Controlador: `@/Users/ignacionovion/Documents/Sistemas/Guardia Nocturna/app/Http/Controllers/Central/AuditController.php`

---

## 5. Soporte Prioritario (`priority_support`)

### ¿Qué significa este addon?

Diferenciación de nivel de atención al cliente. Los tenants con este addon reciben:
- Respuesta garantizada en menos tiempo (ej: 4 horas vs 24 horas)
- Canal de comunicación directo (chat dedicado)
- Escalación prioritaria de bugs críticos
- Acceso a documentación avanzada

### ¿Está implementado actualmente?

**❌ No implementado.** Es un indicador de nivel de servicio sin funcionalidad técnica automática.

### Implementación futura sugerida

**Sistema de tickets integrado:**

```php
// Modelo Ticket
class SupportTicket extends Model
{
    protected $connection = 'central';
    
    public function getResponseTimeAttribute(): int
    {
        // Tickets de tenants con priority_support: 4 horas SLA
        // Tickets normales: 24 horas SLA
        return $this->tenant->hasAddon('priority_support') ? 4 : 24;
    }
}
```

**Badge visual en panel SaaS:**
```blade
@if($tenant->hasAddon('priority_support'))
    <span class="badge badge-gold">⭐ Soporte Prioritario</span>
@endif
```

### ¿Qué valor aporta?

- **Ingresos recurrentes:** Addon de upsell en planes Enterprise
- **Retención:** Clientes con soporte prioritario tienen menor churn
- **Diferenciación:** Justifica precios más altos vs plan Profesional

### ¿Dónde revisarlo?

- Tabla: `tenants` → columna `features` o `addons` JSON
- Feature flag: `priority_support` en modelo Plan
- Futuro: Crear módulo de tickets en `/sas/support`

---

## 6. Webhooks e Integraciones (`webhooks`)

### ¿Qué significa este addon?

Permite configurar URLs de callback para que GuardiAPP notifique a sistemas externos cuando ocurren eventos específicos: nueva emergencia, cambio de guardia, asistencia registrada, etc.

### ¿Está implementado actualmente?

**❌ No implementado.**

### Casos de uso en GuardiAPP

| Evento | Payload | Sistema externo típico |
|--------|---------|------------------------|
| `emergency.created` | Datos de la emergencia | Central de alarmas municipal |
| `attendance.saved` | Resumen de asistencia | Sistema de nómina o RRHH |
| `guardia.week_changed` | Nueva guardia asignada | Calendario institucional |
| `inventory.low_stock` | Material con bajo stock | Sistema de compras/provisiones |
| `volunteer.status_changed` | Cambio de estado | Directorio de bomberos nacional |

### Implementación técnica sugerida

**Tabla de configuración:**

```php
// Migration
Schema::create('webhook_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained('tenants');
    $table->string('event'); // emergency.created
    $table->string('url');   // https://municipalidad.cl/api/guardiapp
    $table->string('secret'); // Para verificar firma HMAC
    $table->boolean('active')->default(true);
    $table->timestamps();
});
```

**Envío de webhooks:**

```php
class WebhookDispatcher
{
    public function dispatch(string $event, array $payload): void
    {
        if (!addonEnabled('webhooks')) return;
        
        $subscriptions = WebhookSubscription::where('event', $event)
            ->where('active', true)
            ->get();
            
        foreach ($subscriptions as $sub) {
            dispatch(new SendWebhookJob($sub, $payload));
        }
    }
}
```

### ¿Dónde revisarlo?

- No existe actualmente
- Futuro: Panel de configuración en `/admin/integrations/webhooks`
- Modelos: `WebhookSubscription`, `WebhookDelivery` (para reintentos)

---

## 7. Single Sign-On (SSO) (`sso`)

### ¿Qué significa este addon?

Permite que los usuarios de GuardiAPP inicien sesión usando credenciales corporativas existentes (Active Directory, Google Workspace, Microsoft 365, etc.) sin necesidad de crear contraseñas específicas para GuardiAPP.

### ¿Está implementado actualmente?

**❌ No implementado.**

### Casos de uso para bomberos

| Proveedor | Caso de uso |
|-----------|-------------|
| **Google Workspace** | Cuerpo de bomberos con Gmail institucional |
| **Microsoft/Azure AD** | Dependencias municipales con Active Directory |
| **SAML 2.0** | Integración con sistemas de gobierno corporativos |
| **OAuth 2.0** | Login con Facebook/Apple (menos común para uso profesional) |

### Implementación técnica

**Laravel Socialite + SAML:**

```php
// Configuración por tenant
Route::get('/login/{provider}', [SsoController::class, 'redirect'])
    ->middleware('addon:sso');

Route::get('/login/{provider}/callback', [SsoController::class, 'callback']);
```

**Configuración almacenada por tenant:**

```php
// SystemSetting::get('sso_config')
[
    'provider' => 'saml',
    'entity_id' => 'https://guardiapp.cl/sp',
    'sso_url' => 'https://municipalidad.cl/idp/sso',
    'certificate' => '-----BEGIN CERTIFICATE-----...',
    'auto_provision_users' => true,
]
```

**User provisioning:** Si un usuario no existe en GuardiAPP pero autentica exitosamente contra el IdP, se puede crear automáticamente con rol por defecto.

### ¿Dónde revisarlo?

- No existe actualmente
- Requiere librerías: `laravel/socialite`, `lightsaml/sp-bundle`
- Configuración: Panel en `/admin/settings/sso`
- Login: Modificación de `resources/views/auth/login.blade.php`

---

## 8. Exportación Masiva de Datos (`data_export`)

### ¿Qué significa este addon?

Permite descargar grandes volúmenes de datos históricos en formatos Excel, CSV o PDF sin las limitaciones de los reportes estándar. Ideal para auditorías externas, análisis estadísticos o migración de datos.

### ¿Está implementado actualmente?

**✅ Parcialmente implementado.**

### Estado actual

**Funcionalidades existentes:**

| Función | Estado | Ubicación |
|---------|--------|-----------|
| Exportar reporte de asistencia (Excel) | ✅ Implementado | `ReportController::attendanceExport()` |
| Exportar reporte de asistencia (PDF) | ✅ Implementado | `ReportController::attendancePdf()` |
| Exportar reportes de reemplazos/refuerzos | ✅ Implementado | Clases en `app/Exports/` |
| Export masivo genérico | ⚠️ Base existe | `GenericReportExport.php` |

**Clases de exportación:**

```php
// app/Exports/
GenericReportExport.php      // Export genérico reutilizable
RefuerzosReportExport.php    // Reporte de refuerzos
ReplacementsReportExport.php // Reporte de reemplazos
```

**Librería utilizada:** `maatwebsite/excel` (Laravel Excel)

### Uso actual en el sistema

```php
// En ReportController.php
public function attendanceExport(Request $request)
{
    // ... validaciones ...
    
    if ($format === 'pdf') {
        return $this->attendancePdf($from, $to, $guardiaId, $currentView);
    }

    // Excel export
    return Excel::download(
        new \App\Exports\GenericReportExport($rows, $headings), 
        'reporte_asistencia_' . $from->format('Ymd') . '.xlsx'
    );
}
```

### Implementación futura sugerida

**Exportaciones adicionales:**

| Tipo | Datos | Formato |
|------|-------|---------|
| Histórico completo | Todos los registros de asistencia | Excel/CSV |
| Inventario total | Todo el inventario con movimientos | Excel |
| Emergencias | Registro de todas las emergencias | PDF/Excel |
| Voluntarios | Directorio completo con especialidades | Excel |
| Planilla anual | Asistencias anuales para contabilidad | Excel |

**Job de export en background:**

```php
class MassExportJob implements ShouldQueue
{
    public function handle()
    {
        if (!addonEnabled('data_export')) {
            throw new \Exception('Exportación masiva requiere addon data_export');
        }
        
        // Generar archivo grande en chunks
        // Notificar por email cuando esté listo
    }
}
```

### ¿Dónde revisarlo?

- Reportes: `/admin/reports` → Botón "Exportar Excel/PDF"
- Código: `@/Users/ignacionovion/Documents/Sistemas/Guardia Nocturna/app/Http/Controllers/ReportController.php:534-588`
- Exports: `@/Users/ignacionovion/Documents/Sistemas/Guardia Nocturna/app/Exports/`
- Config: `config/excel.php`

---

## Tabla Resumen de Addons

| Addon | ¿Existe hoy? | ¿Dónde revisarlo? | ¿Qué valor aporta? | Plan recomendado |
|-------|-------------|-------------------|-------------------|------------------|
| **api_access** | ❌ No | Crear: `routes/api.php` | Integraciones con terceros, apps móviles | Enterprise |
| **custom_branding** | ⚠️ Parcial | `admin/configuracion` | Identidad institucional fortalecida | Profesional+ |
| **backup_automatico** | ✅ Sí | `php artisan tenant:backup` | Seguridad de datos, recuperación ante desastres | Todos (básico manual) |
| **audit_logs** | ✅ Sí | `CentralAuditLog::log()` | Trazabilidad, cumplimiento normativo | Todos (solo SaaS) |
| **priority_support** | ❌ No | Tabla `tenants` | Diferenciación de servicio, upsell | Enterprise |
| **webhooks** | ❌ No | Crear: `WebhookSubscription` | Automatización, integraciones en tiempo real | Enterprise |
| **sso** | ❌ No | Crear: `SsoController` | Seguridad, comodidad de usuario, IT corporativo | Enterprise |
| **data_export** | ⚠️ Parcial | `/admin/reports` → Exportar | Análisis externos, auditorías, respaldo de datos | Profesional+ |

---

## Roadmap de Implementación Recomendado

### Fase 1: Completar existentes (Q1 2026)
1. **custom_branding:** Aplicar colores dinámicos en todas las vistas
2. **data_export:** Agregar más tipos de exportación masiva

### Fase 2: Alto valor, bajo esfuerzo (Q2 2026)
3. **priority_support:** Sistema de tickets básico con SLA
4. **api_access:** API REST v1 con autenticación Sanctum

### Fase 3: Enterprise avanzado (Q3-Q4 2026)
5. **webhooks:** Sistema de suscripción y envío
6. **sso:** Integración SAML 2.0 y OAuth

---

## Consideraciones Técnicas

### Verificación de addons en código

```php
// Usar helper global
if (feature('backup_automatico')) {
    // Ejecutar backup
}

// O método específico para addons
if (addonEnabled('sso')) {
    // Mostrar botones de SSO
}
```

### Base de datos

Los addons se almacenan en el campo JSON `addons` de la tabla `plans`:

```json
{
  "api_access": false,
  "custom_branding": true,
  "backup_automatico": true,
  "audit_logs": false,
  "priority_support": true,
  "webhooks": false,
  "sso": false,
  "data_export": true
}
```

### Overrides por tenant

Los overrides se almacenan en el campo `features` del tenant (hereda de plan pero permite personalización):

```php
$tenant->features = [
    'custom_branding' => true,  // Activar aunque el plan no lo tenga
];
```

---

**Documento generado para GuardiAPP**  
Sistema SaaS Multi-Tenant para Compañías de Bomberos
