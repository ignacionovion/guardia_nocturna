# GuardiAPP SaaS Platform

## Descripción General

GuardiAPP es una plataforma SaaS multi-tenant diseñada para la gestión operativa de compañías de bomberos. Cada compañía (tenant) tiene su propia base de datos aislada, accesible mediante subdominio único.

---

## Arquitectura Multi-Tenant

### Modelo de Aislamiento
- **Base de datos por tenant**: Cada compañía tiene su propia base de datos MySQL (`tenant_{slug}`)
- **Subdominio único**: Acceso via `{slug}.sas.dev-app.cl` (staging) o `{slug}.guardianocturna.cl` (producción)
- **Datos centrales compartidos**: Tabla de tenants, cuerpos de bomberos, y administradores centrales

### Stack Tecnológico
- **Framework**: Laravel 12 + stancl/tenancy v3
- **Base de datos**: MySQL 8.0
- **Frontend**: Blade + Tailwind CSS + Alpine.js
- **Real-time**: Laravel Reverb (WebSockets)
- **Queue**: Laravel Horizon

---

## Panel Central de Administración

### Acceso
- URL: `sas.dev-app.cl/admin` (staging)
- Credenciales: Administradores centrales (tabla `central_admins`)

### Dashboard Principal
- **Métricas globales**: Total tenants, usuarios activos, tamaño total de DBs
- **Estado de salud**: Indicadores por tenant (DB, migraciones, dominios, vencimiento)
- **Alertas**: Tenants próximos a vencer, tenants con problemas
- **Acciones rápidas**: Crear tenant, ver todos, ejecutar migraciones masivas

### Gestión de Compañías (Tenants)

#### Crear Tenant
1. Definir slug único (ej: `quinta-temuco`)
2. Asignar nombre, número de compañía, cuerpo de bomberos
3. Seleccionar plan (Básico, Profesional, Enterprise)
4. Opcionalmente ejecutar seeders iniciales

#### Provisioning Automático
Al crear un tenant, el sistema automáticamente:
1. Crea registro en tabla central `tenants`
2. Crea base de datos `tenant_{slug}`
3. Otorga permisos MySQL al usuario de la app
4. Ejecuta migraciones tenant (83 tablas)
5. Crea dominio/subdominio
6. Opcionalmente ejecuta seeders (usuarios, configuración inicial)

#### Vista de Detalle por Tenant
- **Métricas**: Tamaño DB, usuarios, sesiones activas, almacenamiento, migraciones
- **Health Checks**: Estado de DB, dominio, migraciones, usuarios, vencimiento
- **Feature Flags**: Toggle de funcionalidades por tenant
- **Acciones manuales**: Correr migraciones, ejecutar seeders, abrir app

---

## Sistema de Planes y Feature Flags

### Planes Disponibles

| Feature | Básico | Profesional | Enterprise |
|---------|--------|-------------|------------|
| Emergencias | ✓ | ✓ | ✓ |
| Guardias Nocturnas | ✓ | ✓ | ✓ |
| Gestión de Camas | ✓ | ✓ | ✓ |
| Inventario | ✗ | ✓ | ✓ |
| Dotaciones | ✗ | ✓ | ✓ |
| Reportes Avanzados | ✗ | ✓ | ✓ |
| Acceso API | ✗ | ✗ | ✓ |
| Marca Personalizada | ✗ | ✗ | ✓ |
| Backups Automáticos | ✗ | ✓ | ✓ |
| Máx. Usuarios | 10 | 50 | Ilimitado |

### Feature Flags
- Los valores por defecto provienen del plan asignado
- Se pueden sobrescribir individualmente por tenant (overrides)
- Accesibles en código via `feature('nombre_feature')`
- Middleware `feature:nombre` para proteger rutas

---

## Seguridad y Control de Acceso

### Bloqueo Automático de Tenants
- **Tenants inactivos**: Marcados como `activo = false` muestran página de suspensión
- **Tenants vencidos**: Si `fecha_vencimiento` pasó, acceso bloqueado automáticamente
- Página amigable explicando el motivo y contacto de soporte

### Middleware de Feature Flags
```php
// En rutas
Route::get('/inventario', ...)->middleware('feature:inventario');

// En vistas
@if(feature('reportes'))
    <a href="/reportes">Reportes</a>
@endif
```

---

## Operaciones y Mantenimiento

### Comandos Artisan

#### Migraciones Masivas
```bash
# Migrar todos los tenants activos
php artisan tenant:migrate-all

# Migrar tenant específico
php artisan tenant:migrate-all --tenant=quinta-temuco

# Fresh migration (peligroso)
php artisan tenant:migrate-all --fresh

# Con seeders
php artisan tenant:migrate-all --seed
```

#### Backups
```bash
# Backup de todos los tenants
php artisan tenant:backup

# Backup de tenant específico
php artisan tenant:backup --tenant=quinta-temuco

# Mantener solo últimos N días
php artisan tenant:backup --keep=14
```

#### Ejecutar Comando en Contexto Tenant
```bash
# Ejecutar cualquier comando para todos los tenants
php artisan tenant:run "cache:clear"

# Para tenant específico
php artisan tenant:run "db:seed" --tenant=quinta-temuco
```

### Backups Automáticos
- Programados diariamente a las 03:00 AM
- Formato: `tenant_{slug}_{fecha}.sql.gz`
- Ubicación: `storage/app/backups/`
- Limpieza automática de backups > 7 días

### Logs por Tenant
- Cada tenant tiene su propio archivo de log
- Ubicación: `storage/logs/tenants/{tenant-id}.log`
- Facilita debugging y auditoría por compañía

---

## Gestión de Cuerpos de Bomberos

### Modelo Jerárquico
```
Cuerpo de Bomberos (Body)
└── Compañía 1 (Tenant)
└── Compañía 2 (Tenant)
└── Compañía N (Tenant)
```

### Funcionalidades
- Crear/editar cuerpos de bomberos
- Asignar múltiples compañías a un cuerpo
- Filtrar compañías por cuerpo en el panel central

---

## API y Extensibilidad

### Acceso API (Plan Enterprise)
- Autenticación via Laravel Sanctum
- Endpoints RESTful para integración con sistemas externos
- Rate limiting configurable por tenant

### Webhooks (Futuro)
- Notificaciones de eventos a sistemas externos
- Configurables por tenant

---

## Monitoreo y Métricas

### Métricas por Tenant
- **Tamaño de base de datos**: Bytes ocupados en MySQL
- **Cantidad de tablas**: Verificación de integridad
- **Usuarios registrados**: Total y activos (login últimos 30 días)
- **Sesiones activas**: Usuarios conectados actualmente
- **Almacenamiento**: Archivos subidos por el tenant
- **Migraciones**: Cantidad ejecutadas vs esperadas
- **Último login**: Fecha del último acceso

### Health Checks
| Check | OK | Warning | Error |
|-------|-----|---------|-------|
| Base de datos | Existe y accesible | - | No existe |
| Dominio | Configurado | - | Sin dominio |
| Migraciones | Todas ejecutadas | Faltan algunas | 0 ejecutadas |
| Usuarios | > 0 registrados | - | 0 usuarios |
| Vencimiento | > 30 días | < 30 días | Vencido |

---

## Flujo de Onboarding de Nueva Compañía

1. **Admin central** crea tenant desde panel
2. **Sistema** provisiona DB, migraciones, dominio
3. **Admin central** ejecuta seeders (opcional)
4. **Admin central** comparte URL y credenciales iniciales
5. **Super admin del tenant** configura su compañía
6. **Usuarios** son invitados por el super admin

---

## Configuración de Ambiente

### Variables de Entorno Críticas
```env
# Conexión central
DB_CONNECTION=central

# Dominio central (sin subdominio)
CENTRAL_DOMAIN=sas.dev-app.cl

# Cache (no usar database en multi-tenant)
CACHE_STORE=file

# Sesiones
SESSION_DRIVER=database
```

### Dominios Centrales
Configurados en `config/tenancy.php`:
```php
'central_domains' => [
    env('CENTRAL_DOMAIN', 'localhost'),
    '127.0.0.1',
    'localhost',
],
```

---

## Troubleshooting

### Error 419 PAGE EXPIRED
- Causa: Cambio de dominio entre request (127.0.0.1 vs localhost)
- Solución: Usar siempre el mismo dominio, o configurar `SESSION_DOMAIN`

### Tenant no carga
1. Verificar que el dominio existe en tabla `domains`
2. Verificar que la DB `tenant_{slug}` existe
3. Verificar que las migraciones se ejecutaron
4. Revisar logs en `storage/logs/tenants/{slug}.log`

### Migraciones fallan
```bash
# Ver estado de migraciones
php artisan tenant:run "migrate:status" --tenant=slug

# Forzar migraciones
php artisan tenant:migrate-all --tenant=slug --force
```

---

## Roadmap Futuro

- [ ] Dashboard de métricas con gráficos históricos
- [ ] Webhooks para eventos de tenant
- [ ] Importación/exportación de datos entre tenants
- [ ] Clonación de tenant (para demos/testing)
- [ ] Facturación integrada por plan
- [ ] SSO / SAML para enterprise
