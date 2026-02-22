# Guardia Nocturna - Resumen de Flujos del Sistema

> **Documento generado el:** 21 de Febrero 2026  
> **Versión del código:** Commit 8268951  
> **Ambiente:** Laravel + PHP + MySQL

---

## 1. ARQUITECTURA GENERAL

### 1.1 Estructura de Base de Datos Principal

| Tabla | Descripción | Estado |
|-------|-------------|--------|
| `users` | Usuarios del sistema (admin, guardia, bombero legacy) | ✅ Activa |
| `bomberos` | Voluntarios/Bomberos (modelo principal actual) | ✅ Activa |
| `guardias` | Guardias del sistema (Alpha, Batallion, etc.) | ✅ Activa |
| `reemplazos_bomberos` | Registro de reemplazos entre bomberos | ✅ Activa |
| `shifts` | Turnos de guardia nocturna | ✅ Activa |
| `shift_users` | Asignación de usuarios a turnos | ✅ Activa |
| `beds` | Camas disponibles | ✅ Activa |
| `bed_assignments` | Asignaciones de camas | ✅ Activa |
| `novelties` | Novedades y academias | ✅ Activa |
| `cleaning_tasks` | Tareas de aseo | ✅ Activa |
| `cleaning_assignments` | Asignaciones de aseo | ✅ Activa |
| `mapa_bombero_usuario_legacy` | Mapeo bombero <-> usuario legacy | ⚠️ Legacy |
| `preventive_events` | Eventos preventivos | ✅ Activa |
| `preventive_shifts` | Turnos preventivos | ✅ Activa |
| `preventive_shift_assignments` | Asignaciones preventivas | ✅ Activa |
| `staff_events` | Eventos de personal (sanciones, permisos) | ⚠️ Parcial |
| `guardia_attendance_records` | Registro de guardado de asistencia | ✅ Activa |
| `guardia_calendar_days` | Calendario de guardias semanales | ✅ Activa |
| `system_settings` | Configuraciones del sistema | ✅ Activa |
| `inventory_items` | Items de inventario | ✅ Activa |
| `inventory_movements` | Movimientos de inventario | ✅ Activa |
| `planillas` | Planillas de trabajo | ✅ Activa |

---

## 2. FLUJOS PRINCIPALES DEL SISTEMA

### 2.1 FLUJO DE REEMPLAZOS (CRÍTICO)

**Archivos involucrados:**
- `app/Models/ReemplazoBombero.php`
- `app/Http/Controllers/AdministradorController.php` (assignReplacement, undoReplacement)
- `app/Http/Controllers/TableroController.php` (auto-reset)
- `app/Services/ReplacementService.php`
- `resources/views/dashboard.blade.php`

**Flujo completo:**

```
1. CREAR REEMPLAZO (Admin/Guardia)
   └── POST /admin/guardias/replacement
       ├── Input: bombero_titular_id, bombero_reemplazante_id
       ├── Validaciones:
       │   ├── Titular debe existir y estar en guardia
       │   ├── Reemplazante no puede estar en otra guardia activa
       │   └── Reemplazante no puede estar ya como reemplazante
       ├── Crear registro en reemplazos_bomberos:
       │   ├── guardia_id
       │   ├── bombero_titular_id (titular reemplazado)
       │   ├── bombero_reemplazante_id (quien reemplaza)
       │   ├── inicio = now()
       │   ├── fin = calculateReplacementUntil() [siguiente 07:00 AM]
       │   ├── estado = 'activo'
       │   └── notas = {replacement_previous_guardia_id}
       ├── Actualizar bombero reemplazante:
       │   ├── guardia_id = guardia del titular
       │   ├── es_titular = false
       │   └── estado_asistencia = 'constituye'
       └── Sincronizar a tabla ShiftUser si hay turno activo

2. AUTO-RESET DIARIO (07:00 AM) - TABLEROCONTROLLER
   └── Se ejecuta en cada carga del dashboard si hora >= 07:00
       ├── Identificar bomberos no titulares con updated_at < 07:00 hoy
       ├── Marcar reemplazos como 'completado' en reemplazos_bomberos
       ├── Resetear bombero reemplazante:
       │   └── guardia_id = null (o refuerzo_guardia_anterior_id si era refuerzo)
       └── El titular vuelve a aparecer como 'constituye'

3. DESHACER REEMPLAZO (Manual)
   └── POST /admin/guardias/replacement/{id}/undo
       ├── Cambiar estado a 'cerrado'
       ├── Titular vuelve a 'constituye'
       ├── Reemplazante vuelve a su guardia original
       └── Limpiar campos legacy en tabla users

4. EXPIRACIÓN AUTOMÁTICA (ReplacementService::expire)
   └── Revisar reemplazos con fin <= now()
       ├── Marcar como 'cerrado'
       ├── Restaurar titular
       └── Devolver reemplazante a su guardia
```

**Estados de reemplazo:**
- `activo`: Reemplazo en curso
- `completado`: Finalizado por auto-reset
- `cerrado`: Finalizado manualmente

**Problemas conocidos (RESUELTOS):**
1. ✅ El auto-reset no marcaba los registros como completados (resuelto 21/02)
2. ✅ Los titulares aparecían como "ausente" en lugar de "reemplazado" (resuelto 21/02)

---

### 2.2 FLUJO DE GUARDIAS Y TURNOS

**Archivos involucrados:**
- `app/Models/Guardia.php`
- `app/Models/Shift.php`
- `app/Models/ShiftUser.php`
- `app/Http/Controllers/GuardiaController.php`
- `app/Http/Controllers/AdministradorController.php`

**Flujo de Turno Nocturno:**

```
HORARIO DE GUARDIA:
├── Domingo: 22:00 - 07:00 (próximo día)
├── Lunes-Sábado: 23:00 - 07:00 (próximo día)
└── Corte/Reset: 07:00 AM todos los días

PROCESO DE CONSTITUCIÓN:
1. Guardia se "constituye" al inicio del horario
2. cleanupTransitoriosOnConstitution() remueve no-titulares del turno anterior
3. Solo quedan titulares en la guardia activa

ROLES DE USUARIO:
├── super_admin: Acceso total
├── capitania: Gestión de guardias y reportes
├── guardia: Dashboard propio, asistencia, aseo
└── bombero: ❌ BLOQUEADO (solo para registro QR)
```

**Tabla ShiftUsers (presencia en turno):**
- `shift_id`: Turno al que pertenece
- `firefighter_id`: Referencia a bomberos
- `user_id`: Referencia legacy a users
- `attendance_status`: constituye, reemplazo, permiso, ausente, licencia, falta
- `assignment_type`: Tipo de asignación
- `present`: Boolean de asistencia
- `guardia_id`: Guardia a la que pertenece en ese momento

---

### 2.3 FLUJO DE ASISTENCIA Y CONFIRMACIÓN

**Archivos involucrados:**
- `app/Http/Controllers/AdministradorController.php` (confirmBombero, bulkUpdateGuardia)
- `resources/views/dashboard.blade.php` (JavaScript de confirmación)

**Flujo de confirmación (2 pasos):**

```
1. PREPARAR ASISTENCIA
   └── Usuario cambia estado en dropdown
       ├── Estados disponibles:
       │   ├── constituye (verde) - requiere confirmación
       │   ├── reemplazo (morado) - requiere confirmación
       │   ├── permiso (amarillo)
       │   ├── ausente (gris)
       │   ├── licencia (azul)
       │   └── falta (rojo)
       └── Aparece box "NO CONFIRMADO"

2. CONFIRMAR CON CÓDIGO
   └── Input de número de registro del bombero
       ├── POST /admin/guardias/{guardia}/bomberos/{bombero}/confirm
       ├── Validar código contra bombero->numero_registro
       ├── Generar token HMAC (expira 12 horas)
       └── sessionStorage guarda confirmaciones

3. GUARDAR ASISTENCIA (Bulk)
   └── POST /admin/guardias/{id}/bulk-update
       ├── Validar ventana de tiempo (21:00 - 10:00)
       ├── Validar tokens de confirmación
       ├── Actualizar estado_asistencia en bomberos
       ├── Crear/Actualizar ShiftUser
       └── Guardar GuardiaAttendanceRecord
```

**Reglas de confirmación:**
- Solo `constituye`, `reemplazo`, `refuerzo` requieren confirmación
- `permiso`, `ausente`, `licencia`, `falta` NO requieren confirmación
- Los reemplazantes y refuerzos siempre constituyen (no cambian estado)
- Persistencia en sessionStorage sobrevive refresh

---

### 2.4 FLUJO DE AUTO-RESET (07:00 AM)

**Archivos:** `TableroController.php` (líneas 183-231)

**Lógica de reset:**

```php
Condición: $localNow->greaterThanOrEqualTo($endAt) [07:00 AM]

Pasos:
1. Identificar bomberos NO titulares con updated_at < cutoff
2. Marcar reemplazos como 'completado'
3. Para cada bombero no titular:
   ├── Si es refuerzo: guardia_id = refuerzo_guardia_anterior_id
   └── Si no es refuerzo: guardia_id = null
4. Resetear flags:
   ├── estado_asistencia = 'constituye'
   ├── es_jefe_guardia = false
   ├── es_refuerzo = false
   ├── refuerzo_guardia_anterior_id = null
   ├── es_cambio = false
   └── es_sancion = false
```

**Importante:** Los titulares (`es_titular = true`) NUNCA son removidos de su guardia.

---

### 2.5 FLUJO DE CAMAS

**Archivos:**
- `app/Models/Bed.php`
- `app/Models/BedAssignment.php`
- `app/Http/Controllers/AsignacionCamaController.php`
- `app/Http/Controllers/TableroController.php` (camas)

**Flujo:**

```
ESTADOS DE CAMA:
├── available: Disponible para asignar
├── occupied: Ocupada (tiene asignación activa)
└── maintenance: En mantención (solo super_admin)

ASIGNAR CAMA:
├── POST /camas/asignar
├── Validar que cama esté 'available'
├── Verificar que bombero no tenga otra cama
├── Crear BedAssignment (released_at = null)
├── Actualizar bed->status = 'occupied'
└── Enviar email de notificación

LIBERAR CAMA:
├── PUT /camas/liberar/{id}
├── released_at = now()
├── bed->status = 'available'
└── Enviar email de notificación
```

---

### 2.6 FLUJO DE NOVEDADES Y ACADEMIAS

**Archivos:**
- `app/Models/Novelty.php`
- `app/Http/Controllers/NovedadController.php`

**Tipos:**
- Novedad general: tipo = null
- Academia: tipo = 'Academia'

**Flujo:**
```
POST /novedades
├── title: string
├── description: string
├── type: 'Academia' | null
├── user_id: opcional
├── firefighter_id: opcional
└── date: opcional
```

**Visualización:**
- Dashboard: Últimas 3 novedades + últimas 5 academias
- Sección "Bitácora de Novedades"

---

### 2.7 FLUJO DE ASEO

**Archivos:**
- `app/Models/CleaningTask.php`
- `app/Models/CleaningAssignment.php`
- `app/Http/Controllers/CleaningWebController.php`

**Tareas predefinidas:**
- Aseo Pieza N°1-5
- Aseo Sector Duchas
- Aseo Sector Baños
- Aseo Sala de Estar
- Aseo Cocina Y Quincho

**Flujo:**
```
GET /aseo?date=YYYY-MM-DD
├── Mostrar tareas predefinidas
├── Cargar bomberos con estado_asistencia = 'constituye'
└── Mostrar asignaciones existentes

POST /aseo
├── assigned_date: date
├── assignments: [task_id => firefighter_id]
├── Eliminar asignaciones anteriores para esa fecha
├── Crear nuevas CleaningAssignment
└── Enviar email con resumen
```

---

### 2.8 FLUJO DE INVENTARIO (QR)

**Archivos:**
- `app/Models/InventoryItem.php`
- `app/Models/InventoryWarehouse.php`
- `app/Models/InventoryMovement.php`
- `app/Http/Controllers/Admin/InventarioController.php`
- `app/Http/Controllers/InventarioQrController.php`

**Funcionalidades:**
- Bodegas de inventario
- Items con stock
- QR para retiro de items
- Movimientos de entrada/salida

---

### 2.9 FLUJO DE PREVENTIVAS

**Archivos:**
- `app/Models/PreventiveEvent.php`
- `app/Models/PreventiveShift.php`
- `app/Models/PreventiveShiftAssignment.php`
- `app/Http/Controllers/Admin/PreventiveEventController.php`
- `app/Http/Controllers/PreventivePublicController.php`

**Estados de evento:**
- `draft`: Borrador
- `active`: Activo (visible público)
- `closed`: Cerrado

---

### 2.10 FLUJO DE CALENDARIO DE GUARDIAS

**Archivos:**
- `app/Models/GuardiaCalendarDay.php`
- `app/Http/Controllers/AdminCalendarController.php`

**Lógica:**
```
Semana empieza Domingo (startOfWeek(Carbon::SUNDAY))
└── GuardiaCalendarDay asigna qué guardia está de turno cada semana

Activar semana:
├── POST /admin/calendario/assign-range
├── Desactivar guardia anterior
├── Activar nueva guardia
└── Resetear estado de guardia anterior
```

---

## 3. CÓDIGO LEGACY Y BASURA

### 3.1 TABLA USERS (Legacy)

**Estado:** ⚠️ Parcialmente en uso para compatibilidad

**Campos legacy (aún existen pero sistema usa bomberos):**
- `job_replacement_id`: Usado en migración legacy
- `replacement_until`: Usado en filtrado
- `attendance_status`: Legacy
- `original_*`: Campos de backup para restore
- `is_titular`: Legacy
- `is_refuerzo`: Legacy
- `refuerzo_guardia_anterior_id`: Legacy

**Tabla de mapeo:** `mapa_bombero_usuario_legacy`
- Conecta firefighter_id (bomberos) con user_id (users)
- Usada para sincronización y email

### 3.2 CÓDIGO NO UTILIZADO

**Controladores potencialmente obsoletos:**
- `EventoPersonalController.php`: ¿En uso? Revisar
- `RecordatorioController.php`: ¿En uso?
- `TareaAseoController.php`: API, pero web usa CleaningWebController

**Funciones legacy en models:**
- `User::replacements()`: Relación antigua
- `User::jobReplacement()`: Relación antigua
- Campos `original_*` en users: Para rollback manual

### 3.3 MIGRACIONES A REVISAR

**Tablas que pueden estar obsoletas:**
- `emergencies`, `emergency_keys`, `emergency_units`: ¿En uso?
- `reminders`: ¿En uso?
- `roles`: Sistema de roles legacy (ahora usa campo string)

---

## 4. PERMISOS Y ROLES

### 4.1 Jerarquía de Acceso

| Rol | Dashboard | Guardias | Reportes | Camas | Admin |
|-----|-----------|----------|----------|-------|-------|
| super_admin | ✅ | ✅ | ✅ | ✅ | ✅ |
| capitania | ✅ | ✅ | ✅ | ✅ | ⚠️ Parcial |
| guardia | ✅ Solo propia | ✅ Solo propia | ❌ | ✅ | ❌ |
| bombero | ❌ | ❌ | ❌ | ❌ | ❌ |

### 4.2 Middlewares

- `auth`: Requiere login
- `super_admin`: Solo super admin
- `inventory_access`: Acceso a inventario
- `emergency_access`: Acceso a emergencias
- `preventivas_admin`: Admin de preventivas

---

## 5. CONFIGURACIONES IMPORTANTES

### 5.1 SystemSettings (Claves utilizadas)

| Key | Descripción | Default |
|-----|-------------|---------|
| `guardia_schedule_tz` | Timezone | America/Santiago |
| `guardia_daily_end_time` | Hora de corte | 07:00 |
| `attendance_enable_time` | Inicio asistencia | 21:00 |
| `attendance_disable_time` | Fin asistencia | 10:00 |
| `mail_enabled` | Email activo | - |
| `mail_to_*` | Destinatarios email | - |

### 5.2 Variables de Entorno Críticas

```env
GUARDIA_SCHEDULE_TZ=America/Santiago
APP_KEY=base64:... (Usado para tokens de confirmación)
```

---

## 6. CRONES Y PROCESOS AUTOMÁTICOS

### 6.1 Auto-reset (Dashboard Load)

**Ubicación:** `TableroController::index()`
**Condición:** Cada vez que se carga el dashboard y hora >= 07:00 AM
**Acción:** Resetea reemplazos y refuerzos del turno anterior

### 6.2 Expiración de Reemplazos

**Ubicación:** `ReplacementService::expire()`
**Llamado desde:**
- `AdministradorController::index()`
- `GuardiaController::index()`, `now()`, `nowData()`
- `TableroController::camas()`

### 6.3 Soft Refresh (JavaScript)

**Ubicación:** `dashboard.blade.php` (líneas ~1600)
**Función:** `softRefreshGuardiaDashboard()`
**Interval:** 30 segundos (cuando tab visible)
**Acción:** Refresca HTML parcial del dashboard vía AJAX

---

## 7. FLUJOS DE EMAIL

**Servicio:** `SystemEmailService`

**Tipos de notificación:**
- `beds`: Asignación/liberación de camas
- `cleaning`: Asignación de aseo
- `novelty`: Novedades registradas
- `academy`: Academias registradas
- `replacement`: Reemplazos (implícito)

---

## 8. PROBLEMAS CONOCIDOS Y SOLUCIONES

### 8.1 Resueltos (21/02/2026)

| Problema | Causa | Solución |
|----------|-------|----------|
| Titulares aparecían "AUSENTE" | `$replacementByOriginal` no se verificaba en dashboard | Agregado check en blade |
| Reemplazos no desaparecían a las 07:00 | Auto-reset no marcaba registros como completados | Agregado update a reemplazos_bomberos |

### 8.2 Pendientes de Verificación

1. **ShiftUsers duplicados:** Verificar que no se creen múltiples registros para mismo bombero/shift
2. **Sync legacy-users:** Revisar si aún se necesita sincronización con tabla users
3. **Limpieza de transitorios:** Verificar cleanupTransitoriosOnConstitution en horarios límite

---

## 9. RECOMENDACIONES TÉCNICAS

### 9.1 Refactoring Sugerido

1. **Eliminar código legacy:** Una vez confirmado estable, remover:
   - Tabla `mapa_bombero_usuario_legacy`
   - Campos `original_*` de users
   - Funciones de migración legacy

2. **Consolidar controladores:**
   - Unificar `BomberoController` y lógica en `AdministradorController`
   - Separar concerns de reemplazos a servicio dedicado

3. **Optimizar queries:**
   - Cachear `SystemSettings` (ya implementado)
   - Cachear reemplazos activos por guardia

### 9.2 Testing Recomendado

1. **Flujo crítico a probar:**
   - Crear reemplazo a las 22:00
   - Verificar visualización en dashboard
   - Esperar a 07:00 (o forzar hora)
   - Verificar que reemplazante desaparece
   - Verificar que titular vuelve a "constituye"

2. **Edge cases:**
   - Reemplazo creado DESPUÉS de 07:00 (debería persistir)
   - Múltiples reemplazos simultáneos
   - Deshacer reemplazo manualmente

---

## 10. ANEXOS

### 10.1 Diagrama de Estados - Bombero

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   INICIO    │────▶│  CONSTituye │────▶│  REEMPLAZO  │
│ (nuevo día) │     │   (normal)  │     │ (reemplazado)│
└─────────────┘     └─────────────┘     └──────┬──────┘
      │                    │                    │
      │                    ▼                    │
      │             ┌─────────────┐            │
      │             │   PERMISO   │            │
      │             │   AUSENTE   │            │
      │             │   LICENCIA  │            │
      │             │    FALTA    │            │
      │             └─────────────┘            │
      │                                          │
      │                    07:00 AM              │
      └──────────────────────────────────────────┘
              (Auto-reset: todos a constituye)
```

### 10.2 Diagrama de Estados - Reemplazo

```
┌──────────┐    crear    ┌─────────┐   07:00 AM   ┌────────────┐
│  INICIO  │────────────▶│  ACTIVO │─────────────▶│ COMPLETADO │
└──────────┘             └────┬────┘              └────────────┘
                               │
                               │ undo manual
                               ▼
                         ┌─────────┐
                         │ CERRADO │
                         └─────────┘
```

---

**Fin del documento**

*Para actualizar este documento, revisar los archivos mencionados y verificar que los flujos descritos siguen siendo válidos.*
