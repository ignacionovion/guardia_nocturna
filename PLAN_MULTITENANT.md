# Plan de Trabajo: Sistema Multi-Tenant para Cuerpos de Bomberos

## Contexto del Negocio

El sistema actual fue construido para la **Tercera Compañía del Cuerpo de Bomberos de Temuco**.

Ahora se quiere escalar el sistema para que múltiples compañías y cuerpos de bomberos puedan contratar el servicio de forma flexible.

### Jerarquía real del negocio

```
Tú (dueño del software)
└── Cuerpo de Bomberos de Temuco
│       ├── Primera Compañía
│       ├── Segunda Compañía
│       ├── Tercera Compañía   ← cliente actual
│       └── ...
└── Cuerpo de Bomberos de Osorno
│       ├── Primera Compañía
│       └── Segunda Compañía
└── Tercera Compañía de Valdivia  ← compañía sola, sin cuerpo registrado
```

---

## Decisiones Arquitecturales (Definidas)

### 1. Estructura: Cuerpo → Compañía → Usuarios

La estructura interna principal es:

```
Cuerpo de Bomberos (body)
  └── Compañía (company)
        └── Usuarios / Bomberos
```

**Clave:** Una compañía puede existir **sola** (sin cuerpo) o pertenecer a un cuerpo.

- Si una compañía contrata sola → `body_id = null`
- Si pertenece a un cuerpo → `body_id = 1`

Esto da flexibilidad comercial sin sacrificar la estructura real del negocio.

### 2. Cobro: Por Compañía ✅

- 1 compañía activa = 1 cobro mensual
- 3 compañías activas = 3 cobros mensuales

**Evolución futura:**
- Plan individual por compañía (inicio)
- Plan corporativo por cuerpo (paquetes) → más adelante

Cobrar por cuerpo al inicio genera preguntas complejas (¿cuántas compañías incluye?, ¿qué pasa si se agregan más?). Por compañía es simple, claro y escalable.

### 3. Reportes cruzados: Contemplados desde la base ✅

Los reportes cruzados entre compañías **no se implementan al inicio**, pero la base de datos los soportará desde el día uno.

**Visibilidad por rol:**
- Admin compañía → ve solo su compañía
- Admin cuerpo → ve todas las compañías de su cuerpo
- Superadmin (tú) → ve todo

**Reportes futuros para el Admin del Cuerpo:**
- Asistencia total del cuerpo
- Novedades por compañía
- Estadísticas comparativas
- Dotación activa total
- Guardias más complejas del mes

### 4. WebSockets / Reverb: Aislados por tenant ✅

Cada tenant (compañía) debe estar completamente aislado en los canales de WebSocket.

**Formato de canales:**
```
company.{id}.guardia.updated
company.{id}.notifications
body.{body_id}.company.{company_id}.guardia.updated
```

Nunca usar canales genéricos como `guardia.updated`.

### 5. Dominios: Subdominios por compañía ✅

Usar subdominios por cliente con wildcard DNS `*.tudominio.cl`.

```
app.tudominio.cl           → Landing / marketing
admin.tudominio.cl         → Tu panel maestro (solo tú)
tercera-temuco.tudominio.cl  → Tercera Compañía de Temuco
temuco.tudominio.cl          → Cuerpo de Bomberos de Temuco completo
osorno.tudominio.cl          → Cuerpo de Bomberos de Osorno
```

Requiere configuración de wildcard DNS en el proveedor de dominio.

---

## Estructura de Base de Datos

### Base de datos central (sistema maestro)

```sql
bodies                    -- Cuerpos de Bomberos (opcional, body_id nullable)
  - id
  - nombre                -- "Cuerpo de Bomberos de Temuco"
  - ciudad
  - activo
  - created_at

companies                 -- Compañías (tenant principal)
  - id
  - body_id (nullable)    -- NULL si opera sola, FK si pertenece a un cuerpo
  - nombre                -- "Tercera Compañía"
  - numero                -- 3
  - subdominio            -- "tercera-temuco"
  - plan                  -- "básico" | "profesional" | "enterprise"
  - activo
  - fecha_vencimiento
  - created_at

company_plans
  - id
  - nombre
  - max_usuarios
  - precio_mensual
  - funciones_habilitadas (JSON)

-- Historial de facturación
company_billing
  - id
  - company_id
  - periodo
  - monto
  - pagado
  - fecha_pago
```

### Base de datos por tenant (una por compañía)

```
DB: tenant_tercera_temuco
DB: tenant_primera_osorno
DB: tenant_segunda_valdivia
```

Cada base de datos tiene la misma estructura que el sistema actual. No se necesita `compania_id` en las tablas internas porque cada tenant ya es una compañía aislada.

---

## Roles por nivel

### Nivel 1: Super Admin del Software (tú)
- Acceso a `admin.tudominio.cl`
- Crear/suspender/eliminar compañías y cuerpos
- Definir planes de suscripción
- Ver métricas globales
- Impersonar cualquier tenant para soporte

### Nivel 2: Admin del Cuerpo
- Acceso a `temuco.tudominio.cl`
- Ve todas las compañías de su cuerpo
- Reportes consolidados del cuerpo
- No puede gestionar internamente cada compañía (solo vista)

### Nivel 3: Admin de Compañía (Capitán / Jefe de Guardia)
- Acceso a `tercera-temuco.tudominio.cl`
- Todo lo que el sistema actual ya hace
- Rol actual: `capitania`, `super_admin`

### Nivel 4: Usuario operativo
- Acceso al subdominio de su compañía
- Igual que el sistema actual

---

## Paquete a usar: stancl/tenancy v3

```bash
composer require stancl/tenancy
```

Maneja automáticamente:
- Resolución del tenant por subdominio
- Cambio de conexión de base de datos por request
- Migraciones separadas por tenant
- Comandos artisan para crear/eliminar tenants

---

## Fases del Plan de Trabajo

### Fase 0 — Preparación (sin cambios visibles al usuario)
- [ ] Crear rama `feature/multitenant` en git
- [ ] Instalar y configurar `stancl/tenancy`
- [ ] Definir cuáles migraciones son "centrales" (`bodies`, `companies`, `company_plans`) y cuáles son "por tenant" (todo lo actual)
- [ ] Crear base de datos central `app_master`
- [ ] Configurar wildcard DNS `*.tudominio.cl`
- [ ] Verificar que el sistema actual sigue funcionando igual

**Estimado: 1-2 días**

---

### Fase 1 — Panel del Super Admin (tú)
- [ ] Crear subdominio `admin.tudominio.cl`
- [ ] Login separado para el super admin (tabla `admin_users`)
- [ ] CRUD de Companies + Bodies
- [ ] Gestión de planes y vencimientos
- [ ] Vista de estado de todos los tenants

**Estimado: 3-4 días**

---

### Fase 2 — Adaptar sistema actual a multi-tenant
- [ ] Mover todas las migraciones actuales a `database/migrations/tenant/`
- [ ] Adaptar resolución de tenant por subdominio
- [ ] Adaptar canales de WebSocket al formato `company.{id}.*`
- [ ] Verificar que los controllers no filtren por tenant manualmente (ya lo hace el DB switch)
- [ ] Adaptar el layout/navbar para mostrar nombre de compañía por tenant

**Estimado: 4-6 días**

---

### Fase 3 — Reportes del Admin del Cuerpo (base)
- [ ] Crear panel básico para Admin del Cuerpo en `{cuerpo}.tudominio.cl`
- [ ] Vista de compañías activas dentro del cuerpo
- [ ] Reporte de asistencia consolidada (lectura cruzada de DBs)
- [ ] Estadísticas básicas por compañía

**Estimado: 3-4 días**

---

### Fase 4 — Onboarding de nuevos clientes
- [ ] Comando/botón para crear nuevo tenant + base de datos automáticamente
- [ ] Seed inicial con roles y configuración default
- [ ] Email de bienvenida con credenciales al admin de la compañía
- [ ] Wizard de configuración inicial (nombre, número de compañía, primer admin)

**Estimado: 2-3 días**

---

### Fase 5 — Migrar cliente actual (Tercera Compañía Temuco)
- [ ] Crear tenant `tercera-temuco`
- [ ] Migrar datos actuales a la base de datos del tenant
- [ ] Cambiar URL a `tercera-temuco.tudominio.cl`
- [ ] Verificar que todo funciona igual para el cliente actual

**Estimado: 1-2 días**

---

### Fase 6 — Billing / Suscripciones (futuro)
- [ ] Integrar pasarela de pagos (Khipu, Transbank, Stripe)
- [ ] Automatizar suspensión por no pago
- [ ] Panel de facturación por compañía
- [ ] Plan corporativo por cuerpo (descuento por múltiples compañías)

**Estimado: depende de la pasarela elegida**

---

## Resumen de esfuerzo total estimado

| Fase | Descripción | Días estimados |
|------|-------------|---------------|
| 0 | Preparación | 1-2 |
| 1 | Panel Super Admin | 3-4 |
| 2 | Adaptar sistema | 4-6 |
| 3 | Reportes Admin Cuerpo | 3-4 |
| 4 | Onboarding | 2-3 |
| 5 | Migrar Temuco | 1-2 |
| **Total** | | **14-21 días** |

---

## Lo que NO cambia para los usuarios finales

Los bomberos, capitanes y jefes de guardia **no notarán ningún cambio funcional**. La interfaz, funcionalidades y flujos son exactamente los mismos. Solo cambia la URL (de la actual a `tercera-temuco.tudominio.cl`).

---

## Notas técnicas clave

- `body_id` es **nullable** en `companies` → una compañía puede vivir sola o dentro de un cuerpo
- Cada tenant tiene su **propia base de datos** → aislamiento total de datos
- Los canales de WebSocket deben incluir **siempre el `company_id`** para evitar eventos cruzados
- Los reportes cruzados del Admin del Cuerpo requieren **conexiones a múltiples DBs** → implementar con cuidado en Fase 3
- El wildcard DNS **debe configurarse antes** de avanzar en subdominios
