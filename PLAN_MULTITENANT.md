# Plan de Trabajo: Sistema Multi-Tenant para Cuerpos de Bomberos

## Contexto del Negocio

El sistema actual fue construido para la **Tercera Compañía del Cuerpo de Bomberos de Temuco**.

Ahora se quiere escalar el sistema para que múltiples **Cuerpos de Bomberos** (de distintas regiones/ciudades) puedan contratar el servicio, y cada uno tiene internamente múltiples **Compañías**.

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
└── Cuerpo de Bomberos de Valdivia
        └── ...
```

---

## Decisión de Arquitectura: ¿Tenant = Compañía o Tenant = Cuerpo?

Esta es la decisión más importante del plan.

### Opción A: Tenant = Compañía (granular)

Cada compañía (Primera, Segunda, Tercera...) es un tenant independiente.

- `tercera-temuco.tuapp.cl`
- `primera-temuco.tuapp.cl`
- `primera-osorno.tuapp.cl`

**Pros:**
- Aislamiento total entre compañías
- Cada compañía paga por separado

**Contras:**
- No hay visión consolidada por Cuerpo
- Un Cuerpo con 8 compañías = 8 tenants que gestionar
- Difícil reportar a nivel de Cuerpo completo

---

### Opción B: Tenant = Cuerpo de Bomberos ✅ RECOMENDADA

Cada **Cuerpo de Bomberos** es un tenant. Las compañías son entidades internas dentro del tenant.

- `temuco.tuapp.cl` → Cuerpo de Temuco (con todas sus compañías adentro)
- `osorno.tuapp.cl` → Cuerpo de Osorno
- `valdivia.tuapp.cl` → Cuerpo de Valdivia

**Pros:**
- Refleja la jerarquía real del negocio
- El Comandante del Cuerpo puede ver todas sus compañías
- Un solo contrato por Cuerpo
- Más simple de vender y gestionar
- Reportes consolidados por Cuerpo posibles

**Contras:**
- Compañías del mismo Cuerpo comparten la misma base de datos (pero con `compania_id`)
- Si una compañía quiere comprar sin el resto del Cuerpo, se complica

**→ Se elige esta opción porque es la que mejor refleja la realidad operativa de los Cuerpos de Bomberos en Chile.**

---

## Estructura de URLs

```
app.tudominio.cl           → Landing / marketing
admin.tudominio.cl         → Tu panel maestro (solo tú)
temuco.tudominio.cl        → Cuerpo de Bomberos de Temuco
osorno.tudominio.cl        → Cuerpo de Bomberos de Osorno
valdivia.tudominio.cl      → Cuerpo de Bomberos de Valdivia
```

---

## Roles por nivel

### Nivel 1: Super Admin del Software (tú)
- Acceso a `admin.tudominio.cl`
- Crear/suspender/eliminar Cuerpos de Bomberos (tenants)
- Definir planes de suscripción
- Ver métricas globales (cantidad de usuarios, guardias, etc.)
- Impersonar cualquier tenant para soporte

### Nivel 2: Admin del Cuerpo (Comandante o TI del Cuerpo)
- Acceso a `temuco.tudominio.cl`
- Crear/gestionar sus Compañías dentro del Cuerpo
- Crear usuarios admin por compañía
- Ver reportes de todas las compañías
- Configurar parámetros del Cuerpo

### Nivel 3: Admin de Compañía (Capitán / Jefe de Guardia)
- Acceso a `temuco.tudominio.cl` pero solo ve su compañía
- Todo lo que el sistema actual ya hace (guardias, nómina, camas, etc.)
- Rol actual: `capitania`, `super_admin` de la compañía

### Nivel 4: Usuario operativo (Guardia, Inventario, etc.)
- Acceso a `temuco.tudominio.cl`
- Igual que el sistema actual

---

## Estructura de Base de Datos

### Base de datos central (solo del sistema maestro)
```sql
tenants           -- Cuerpos de Bomberos registrados
  - id
  - nombre        -- "Cuerpo de Bomberos de Temuco"
  - subdominio    -- "temuco"
  - plan          -- "básico" | "profesional" | "enterprise"
  - activo
  - fecha_vencimiento
  - created_at

tenant_plans
  - id
  - nombre
  - max_companias
  - max_usuarios
  - precio_mensual
  - funciones_habilitadas (JSON)
```

### Base de datos por tenant (una por Cuerpo de Bomberos)
```
DB: tenant_temuco
DB: tenant_osorno
DB: tenant_valdivia
```

Cada base de datos tiene la misma estructura que el sistema actual, más:
```sql
companias         -- Nueva tabla
  - id
  - nombre        -- "Tercera Compañía"
  - numero        -- 3
  - color
  - activo

-- Todas las tablas existentes agregan:
bomberos          + compania_id
guardias          + compania_id
novedades         + compania_id
(etc.)
```

---

## Paquete a usar: stancl/tenancy v3

```bash
composer require stancl/tenancy
```

Este paquete maneja automáticamente:
- Resolución del tenant por subdominio
- Cambio de conexión de base de datos por request
- Migraciones separadas por tenant
- Comandos artisan para crear/eliminar tenants

---

## Fases del Plan de Trabajo

### Fase 0 — Preparación (sin cambios visibles al usuario)
- [ ] Crear rama `feature/multitenant` en git
- [ ] Instalar y configurar `stancl/tenancy`
- [ ] Definir cuáles migraciones son "centrales" y cuáles son "por tenant"
- [ ] Crear base de datos central `app_master`
- [ ] Verificar que el sistema actual sigue funcionando igual

**Estimado: 1-2 días**

---

### Fase 1 — Panel del Super Admin (tú)
- [ ] Crear subdominio `admin.tudominio.cl`
- [ ] Login separado para el super admin (tabla `admin_users`)
- [ ] CRUD de Tenants (Cuerpos de Bomberos)
- [ ] Gestión de planes y vencimientos
- [ ] Vista de estado de todos los tenants

**Estimado: 3-4 días**

---

### Fase 2 — Adaptar sistema actual a multi-tenant
- [ ] Mover todas las migraciones actuales a `database/migrations/tenant/`
- [ ] Agregar tabla `companias` dentro del schema del tenant
- [ ] Agregar `compania_id` a las tablas que lo necesiten
- [ ] Adaptar los controllers para filtrar por `compania_id`
- [ ] Adaptar el dashboard para que el Admin del Cuerpo vea todas sus compañías

**Estimado: 5-7 días** (es la fase más grande)

---

### Fase 3 — Onboarding de nuevos Cuerpos
- [ ] Comando/botón para crear nuevo tenant + base de datos automáticamente
- [ ] Seed inicial con datos básicos (roles, configuración default)
- [ ] Email de bienvenida con credenciales al admin del Cuerpo
- [ ] Wizard de configuración inicial (nombre Cuerpo, compañías, primer admin)

**Estimado: 2-3 días**

---

### Fase 4 — Migrar cliente actual (Temuco)
- [ ] Crear tenant `temuco`
- [ ] Migrar datos actuales a la base de datos del tenant
- [ ] Cambiar URL a `temuco.tudominio.cl`
- [ ] Verificar que todo funciona igual para el cliente actual

**Estimado: 1-2 días**

---

### Fase 5 — Billing / Suscripciones (opcional futuro)
- [ ] Integrar pasarela de pagos (Khipu, Transbank, Stripe)
- [ ] Automatizar suspensión por no pago
- [ ] Panel de facturación por tenant

**Estimado: depende de la pasarela elegida**

---

## Preguntas a definir antes de empezar

1. **¿Quieres que cada Compañía pueda operar independiente, o siempre dentro de un Cuerpo?**
   - Si una compañía quiere contratar sola sin el resto del Cuerpo, ¿se lo permites?

2. **¿El plan de suscripción es por Cuerpo o por Compañía?**
   - Por Cuerpo: un precio, todas las compañías incluidas
   - Por Compañía: precio x compañía activa

3. **¿Los Cuerpos necesitan reportes cruzados entre compañías?**
   - Ej: "¿Cuántas emergencias tuvo el Cuerpo de Temuco en total este mes?"

4. **¿Reverb (WebSockets) también debe ser por tenant?**
   - Sí, cada tenant necesita su propio canal de notificaciones aislado

5. **¿Qué dominio vas a usar para el producto?**
   - Necesitas un dominio con wildcard DNS `*.tudominio.cl`

---

## Resumen de esfuerzo total estimado

| Fase | Días estimados |
|------|---------------|
| 0 - Preparación | 1-2 |
| 1 - Panel Super Admin | 3-4 |
| 2 - Adaptar sistema | 5-7 |
| 3 - Onboarding | 2-3 |
| 4 - Migrar Temuco | 1-2 |
| **Total** | **12-18 días** |

---

## Lo que NO cambia para los usuarios finales

Los bomberos, capitanes y jefes de guardia de cada compañía **no notarán ningún cambio**. La interfaz, funcionalidades y flujos son exactamente los mismos. Solo cambia la URL (de `app.cl` a `temuco.app.cl`).
