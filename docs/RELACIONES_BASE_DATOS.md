# Relaciones de Base de Datos - Guardia Nocturna

## Resumen Ejecutivo

Este documento describe todas las relaciones de la base de datos del sistema Guardia Nocturna, incluyendo:
- Diagrama ER (Entidad-Relación)
- Tablas y sus columnas principales
- Relaciones entre tablas (1:1, 1:N, N:M)
- Relaciones Eloquent (Modelos ↔ Tablas)

---

## Diagrama ER - Vista General

```
┌─────────────────┐     1:N     ┌─────────────────┐
│    guardias     │◄────────────│     users       │
│   (Guardias)    │             │  (Usuarios)     │
└────────┬────────┘             └────────┬────────┘
         │                                 │
         │ 1:N                             │ 1:N (legacy)
         ▼                                 ▼
┌─────────────────┐     1:N     ┌─────────────────┐
│    bomberos     │◄────────────│  shift_users    │
│  (Bomberos)     │             │ (Turno-User)  │
└────────┬────────┘             └────────┬────────┘
         │                                 │
         │ 1:N                             │ N:1
         ▼                                 ▼
┌─────────────────┐              ┌─────────────────┐
│reemplazos_bomber│              │     shifts      │
│   (Reemplazos)  │              │    (Turnos)     │
└─────────────────┘              └─────────────────┘
         │
         │ 1:N
         ▼
┌─────────────────┐
│ bed_assignments │
│ (Asignaciones   │
│   de camas)     │
└────────┬────────┘
         │ N:1
         ▼
┌─────────────────┐
│      beds       │
│    (Camas)      │
└─────────────────┘

┌─────────────────┐              ┌─────────────────┐
│    novelties    │              │    academies    │
│  (Novedades)    │              │   (Academias)   │
└────────┬────────┘              └────────┬────────┘
         │                                 │
         │ N:1                             │ N:1
         ▼                                 ▼
┌─────────────────┐              ┌─────────────────┐
│     users       │              │    bomberos     │
└─────────────────┘              └─────────────────┘
```

---

## Tablas Principales y Relaciones

### 1. **users** (Usuarios del Sistema)

**Propósito**: Autenticación y datos básicos de usuarios (legacy + nuevos).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| name | string | Nombre completo |
| email | string | Email único |
| password | string | Hash contraseña |
| role | enum | super_admin, capitania, guardia, inventario, ayudante |
| role_id | bigint | FK → roles |
| guardia_id | bigint | FK → guardias (a qué guardia pertenece) |
| birthdate | date | Fecha nacimiento |
| ... | ... | Más campos de perfil |

**Relaciones Eloquent** (`User` model):
```php
// No tiene relaciones definidas explícitamente en el modelo
// Pero se usa en:
- Shift::leader()  // Jefe de turno
- Novelty::user()  // Creador de novedad
- Emergency::officerInCharge()  // Oficial a cargo
```

---

### 2. **guardias** (Guardias Nocturnas)

**Propósito**: Grupos/escuadrones de bomberos.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| name | string | Nombre de la guardia |
| is_active_week | boolean | ¿Es la guardia activa de la semana? |

**Relaciones**:
- **1:N** → `users` (usuarios pertenecen a una guardia)
- **1:N** → `bomberos` (bomberos pertenecen a una guardia)
- **1:N** → `novelties` (novedades de la guardia)
- **1:N** → `reemplazos_bomberos` (reemplazos de la guardia)
- **1:N** → `emergencies` (emergencias atendidas por la guardia)
- **1:N** → `preventive_shifts` (turnos preventivos de la guardia)

**Eloquent** (`Guardia` model):
```php
public function users()      // hasMany(User::class)
public function bomberos()   // hasMany(Bombero::class)
public function firefighters() // alias de bomberos()
```

---

### 3. **bomberos** (Bomberos - Tabla Principal)

**Propósito**: Datos completos de cada bombero. Es la tabla principal de personal.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| guardia_id | bigint | FK → guardias |
| nombres | string | Nombres |
| apellido_paterno | string | Apellido paterno |
| apellido_materno | string | Apellido materno |
| rut | string | RUT único |
| correo | string | Email |
| fecha_nacimiento | date | Fecha nacimiento |
| fecha_ingreso | date | Fecha ingreso a la compañía |
| cargo_texto | string | Cargo/descripción |
| numero_portatil | string | Número móvil |
| es_conductor | boolean | ¿Es conductor? |
| conductor_carros_bomba | boolean | ¿Conduce carros bomba? |
| es_operador_rescate | boolean | ¿Operador de rescate? |
| es_asistente_trauma | boolean | ¿Asistente de trauma? |
| es_jefe_guardia | boolean | ¿Es jefe de guardia? |
| es_permanente | boolean | ¿Bombero permanente? |
| es_refuerzo | boolean | ¿Es refuerzo temporal? |
| es_titular | boolean | ¿Es titular (no transitorio)? |
| estado_asistencia | enum | constituye, reemplazo, permiso, ausente, falta, licencia |
| fuera_de_servicio | boolean | ¿Está inhabilitado? |
| photo_path | string | Foto de perfil |

**Relaciones**:
- **N:1** → `guardias` (pertenece a una guardia)
- **1:N** → `shift_users` (asignaciones a turnos)
- **1:N** → `bed_assignments` (asignaciones de camas)
- **1:N** → `reemplazos_bomberos` (como titular original)
- **1:N** → `reemplazos_bomberos` (como reemplazante)
- **1:N** → `novelties` (novedades asociadas al bombero)
- **1:1** → `mapa_bombero_usuario_legacy` (mapeo a tabla users legacy)

**Eloquent** (`Bombero` model):
```php
public function guardia()           // belongsTo(Guardia::class)
public function shiftUsers()        // hasMany(ShiftUser::class)
public function legacyUserMap()     // hasOne(MapaBomberoUsuarioLegacy::class)
```

---

### 4. **shifts** (Turnos/Guardias Activas)

**Propósito**: Registro de turnos de guardia (constitución de la guardia).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| date | date | Fecha del turno |
| shift_leader_id | bigint | FK → users (jefe de guardia) |
| status | enum | active, closed |
| notes | text | Notas |

**Relaciones**:
- **1:N** → `shift_users` (usuarios en el turno)
- **N:1** → `users` (jefe de guardia)
- **1:N** → `emergencies` (emergencias durante el turno)

**Eloquent** (`Shift` model):
```php
public function leader()     // belongsTo(User::class, 'shift_leader_id')
public function users()      // hasMany(ShiftUser::class)
```

---

### 5. **shift_users** (Asignación Turno-Usuario)

**Propósito**: Tabla pivote entre turnos y usuarios/bomberos. Registra quién está en cada turno.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| shift_id | bigint | FK → shifts |
| user_id | bigint | FK → users (legacy, nullable) |
| firefighter_id | bigint | FK → bomberos (nueva tabla) |
| guardia_id | bigint | FK → guardias |
| role | string | Rol en el turno (driver, medic) |
| present | boolean | ¿Está presente? |
| attendance_status | string | Estado de asistencia |
| assignment_type | string | Tipo de asignación |
| replaced_user_id | bigint | FK → users (si reemplaza a alguien) |
| replaced_firefighter_id | bigint | FK → bomberos (si reemplaza a alguien) |
| start_time | datetime | Inicio en el turno |
| end_time | datetime | Fin en el turno (null = sigue activo) |

**Relaciones**:
- **N:1** → `shifts`
- **N:1** → `users` (legacy)
- **N:1** → `bomberos` (actual)
- **N:1** → `guardias`
- **N:1** → `users` (replaced_user_id)
- **N:1** → `bomberos` (replaced_firefighter_id)

**Eloquent** (`ShiftUser` model):
```php
public function shift()              // belongsTo(Shift::class)
public function user()                 // belongsTo(User::class)
public function firefighter()          // belongsTo(Bombero::class)
public function replacedUser()         // belongsTo(User::class, 'replaced_user_id')
public function replacedFirefighter()    // belongsTo(Bombero::class, 'replaced_firefighter_id')
```

---

### 6. **reemplazos_bomberos** (Reemplazos)

**Propósito**: Registra reemplazos temporales de bomberos.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| guardia_id | bigint | FK → guardias |
| bombero_titular_id | bigint | FK → bomberos (el que es reemplazado) |
| bombero_reemplazante_id | bigint | FK → bomberos (el reemplazante) |
| inicio | datetime | Inicio del reemplazo |
| fin | datetime | Fin del reemplazo (null = indefinido) |
| estado | enum | active, closed |
| notas | text | Notas |

**Relaciones**:
- **N:1** → `guardias`
- **N:1** → `bomberos` (bombero_titular_id)
- **N:1** → `bomberos` (bombero_reemplazante_id)

**Eloquent** (`ReemplazoBombero` model):
```php
public function guardia()                // belongsTo(Guardia::class)
public function originalFirefighter()      // belongsTo(Bombero::class, 'bombero_titular_id')
public function replacementFirefighter() // belongsTo(Bombero::class, 'bombero_reemplazante_id')
```

---

### 7. **beds** (Camas/Dormitorios)

**Propósito**: Inventario de camas disponibles.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| number | string | Número de cama |
| status | enum | available, occupied, maintenance |
| description | text | Descripción/notas |

**Relaciones**:
- **1:N** → `bed_assignments` (asignaciones de la cama)
- **1:1** (virtual) → `currentAssignment` (asignación actual activa)

**Eloquent** (`Bed` model):
```php
public function assignments()        // hasMany(BedAssignment::class)
public function currentAssignment()  // hasOne(BedAssignment::class)->whereNull('released_at')
```

---

### 8. **bed_assignments** (Asignaciones de Camas)

**Propósito**: Registra quién ocupa cada cama y cuándo.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| bed_id | bigint | FK → beds |
| user_id | bigint | FK → users (legacy, nullable) |
| firefighter_id | bigint | FK → bomberos (actual) |
| assigned_at | datetime | Fecha/hora de asignación |
| released_at | datetime | Fecha/hora de liberación (null = ocupada) |
| notes | text | Notas |

**Relaciones**:
- **N:1** → `beds`
- **N:1** → `users` (legacy)
- **N:1** → `bomberos` (actual)

**Eloquent** (`BedAssignment` model):
```php
public function bed()           // belongsTo(Bed::class)
public function user()          // belongsTo(User::class)
public function firefighter()   // belongsTo(Bombero::class)
```

---

### 9. **novelties** (Novedades/Bitácora)

**Propósito**: Registro de novedades, incidentes y academias.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| user_id | bigint | FK → users (quien registra) |
| firefighter_id | bigint | FK → bomberos (opcional, a quién afecta) |
| guardia_id | bigint | FK → guardias (qué guardia) |
| title | string | Título |
| description | text | Descripción |
| date | datetime | Fecha de la novedad |
| type | string | Tipo: Informativa, Incidente, Mantención, Urgente, Permanente, Academia |
| is_permanent | boolean | ¿Visible para todas las guardias? |

**Relaciones**:
- **N:1** → `users` (creador)
- **N:1** → `bomberos` (bombero asociado, para academias)
- **N:1** → `guardias` (guardia asociada)

**Eloquent** (`Novelty` model):
```php
public function user()          // belongsTo(User::class)
public function firefighter()   // belongsTo(Bombero::class)
public function guardia()       // belongsTo(Guardia::class)

// Scopes útiles:
scopePermanent()       // Novedades permanentes
scopeByGuardia($id)    // Novedades de una guardia + permanentes
scopeNotAcademy()      // Excluir academias
scopeAcademy()         // Solo academias
```

---

### 10. **emergencies** (Emergencias)

**Propósito**: Registro de emergencias atendidas.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| emergency_key_id | bigint | FK → emergency_keys (tipo de emergencia) |
| dispatched_at | datetime | Hora de despacho |
| arrived_at | datetime | Hora de llegada |
| details | text | Detalles |
| shift_id | bigint | FK → shifts (turno durante el cual ocurrió) |
| guardia_id | bigint | FK → guardias (qué guardia atendió) |
| officer_in_charge_user_id | bigint | FK → users (oficial a cargo, legacy) |
| officer_in_charge_firefighter_id | bigint | FK → bomberos (oficial a cargo) |
| created_by | bigint | FK → users (quién registró) |

**Relaciones**:
- **N:1** → `emergency_keys`
- **N:1** → `shifts`
- **N:1** → `guardias`
- **N:1** → `users` (oficial a charge, creador)
- **N:1** → `bomberos` (oficial a cargo)
- **N:M** → `emergency_units` (unidades que respondieron)

**Eloquent** (`Emergency` model):
```php
public function key()                      // belongsTo(EmergencyKey::class)
public function units()                    // belongsToMany(EmergencyUnit::class)
public function shift()                    // belongsTo(Shift::class)
public function guardia()                  // belongsTo(Guardia::class)
public function officerInCharge()          // belongsTo(User::class)
public function officerInChargeFirefighter() // belongsTo(Bombero::class)
public function creator()                  // belongsTo(User::class, 'created_by')
```

---

### 11. **preventive_shifts** (Turnos Preventivos)

**Propósito**: Turnos para eventos preventivos.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| preventive_event_id | bigint | FK → preventive_events |
| name | string | Nombre del turno |
| start_time | time | Hora inicio |
| end_time | time | Hora fin |
| slots | int | Cupos disponibles |

**Relaciones**:
- **N:1** → `preventive_events`
- **1:N** → `preventive_shift_assignments` (asignaciones)

---

### 12. **preventive_shift_assignments** (Asignaciones Preventivas)

**Propósito**: Asignación de bomberos a turnos preventivos.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| preventive_shift_id | bigint | FK → preventive_shifts |
| firefighter_id | bigint | FK → bomberos |
| assigned_by | bigint | FK → users |
| es_refuerzo | boolean | ¿Es refuerzo? |
| reemplaza_a | bigint | FK → bomberos (si reemplaza a alguien) |

**Relaciones**:
- **N:1** → `preventive_shifts`
- **N:1** → `bomberos` (asignado)
- **N:1** → `users` (quien asignó)
- **N:1** → `bomberos` (reemplaza_a)

---

### 13. **inventory_items** & **inventory_movements** (Inventario)

**Propósito**: Gestión de inventario de equipos.

| Tabla | Columnas Principales | Relaciones |
|-------|---------------------|------------|
| inventory_warehouses | id, name, description | 1:N → items |
| inventory_items | id, warehouse_id, code, name, quantity | N:1 → warehouse, 1:N → movements |
| inventory_movements | id, item_id, bombero_id, type, quantity | N:1 → item, N:1 → bombero |
| inventory_qr_links | id, item_id, qr_token | 1:1 → item |

---

### 14. **planillas** (Planillas/Registros)

**Propósito**: Planillas de asistencia/firma.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint | PK |
| nombre | string | Nombre de la planilla |
| estado | enum | borrador, publicada, cerrada |
| public_slug | string | Slug público para QR |
| turno_guardia_id | bigint | FK → guardias |

---

## Tablas de Soporte

### 15. **system_settings** (Configuración del Sistema)

| Columna | Descripción |
|---------|-------------|
| key | Nombre del setting |
| value | Valor |
| type | Tipo: string, number, boolean, json |

Ejemplos:
- `attendance_enable_time`: Hora inicio asistencia (21:00)
- `attendance_disable_time`: Hora fin asistencia (10:00)
- `guardia_schedule_tz`: Timezone (America/Santiago)

### 16. **roles** (Roles del Sistema)

| Columna | Descripción |
|---------|-------------|
| id | PK |
| name | Nombre: super_admin, capitania, guardia, inventario, ayudante |

### 17. **emergency_keys** & **emergency_units**

| Tabla | Propósito |
|-------|-----------|
| emergency_keys | Tipos de emergencia (10-4, 10-5, etc.) |
| emergency_units | Unidades/vehículos (B-3, RB-1, etc.) |
| emergency_emergency_unit | Pivot: qué unidades respondieron a cada emergencia |

---

## Mapa de Relaciones Completo

### Relaciones 1:N (Uno a Muchos)

| Padre | Hijo | Foreign Key en Hijo |
|-------|------|---------------------|
| guardias | users | guardia_id |
| guardias | bomberos | guardia_id |
| guardias | novelties | guardia_id |
| guardias | reemplazos_bomberos | guardia_id |
| guardias | emergencies | guardia_id |
| guardias | preventive_shifts | (vía preventive_events) |
| users | shifts | shift_leader_id |
| users | novelties | user_id |
| users | emergencies | officer_in_charge_user_id, created_by |
| users | preventive_shift_assignments | assigned_by |
| bomberos | shift_users | firefighter_id |
| bomberos | bed_assignments | firefighter_id |
| bomberos | novelties | firefighter_id |
| bomberos | reemplazos_bomberos | bombero_titular_id, bombero_reemplazante_id |
| bomberos | preventive_shift_assignments | firefighter_id, reemplaza_a |
| bomberos | inventory_movements | bombero_id |
| shifts | shift_users | shift_id |
| shifts | emergencies | shift_id |
| beds | bed_assignments | bed_id |
| preventive_events | preventive_shifts | preventive_event_id |
| preventive_shifts | preventive_shift_assignments | preventive_shift_id |
| emergency_keys | emergencies | emergency_key_id |
| inventory_warehouses | inventory_items | warehouse_id |
| inventory_items | inventory_movements | item_id |

### Relaciones 1:1 (Uno a Uno)

| Tabla A | Tabla B | Descripción |
|---------|---------|-------------|
| beds | bed_assignments | currentAssignment (lógica: released_at IS NULL) |
| bomberos | mapa_bombero_usuario_legacy | Mapeo legacy users ↔ bomberos |
| inventory_items | inventory_qr_links | Código QR del item |

### Relaciones N:M (Muchos a Muchos)

| Tabla A | Tabla B | Tabla Pivot | Descripción |
|---------|---------|-------------|-------------|
| emergencies | emergency_units | emergency_emergency_unit | Unidades que respondieron a emergencia |

### Relaciones Polimórficas (Ninguna actualmente)

El sistema no usa relaciones polimórficas de Eloquent.

---

## Flujo de Datos Típico

### Ejemplo 1: Constitución de Guardia

```
1. Se crea un registro en shifts (fecha actual, jefe de guardia, status=active)
2. Para cada bombero que constituye:
   - Se crea shift_users con shift_id, firefighter_id, guardia_id
   - attendance_status = 'constituye'
3. Si hay reemplazos:
   - Se crea reemplazos_bomberos con bombero_titular_id y bombero_reemplazante_id
4. Se registran novedades en novelties (type='Informativa', etc.)
```

### Ejemplo 2: Asignación de Cama

```
1. Usuario selecciona cama disponible (beds.status = 'available')
2. Se crea bed_assignments:
   - bed_id = ID de la cama
   - firefighter_id = ID del bombero
   - assigned_at = now()
   - released_at = null
3. beds.status cambia a 'occupied'
4. Al liberar:
   - bed_assignments.released_at = now()
   - beds.status = 'available'
```

### Ejemplo 3: Reemplazo de Bombero

```
1. Usuario inicia reemplazo en dashboard
2. Se crea reemplazos_bomberos:
   - bombero_titular_id = ID del bombero original
   - bombero_reemplazante_id = ID del reemplazante
   - inicio = now()
   - estado = 'active'
3. bombero_titular.estado_asistencia = 'reemplazo'
4. bombero_reemplazante pasa a la guardia (guardia_id = X)
5. Al finalizar:
   - reemplazos_bomberos.fin = now()
   - reemplazos_bomberos.estado = 'closed'
```

---

## Notas Importantes

### Dualidad de Tablas (Legacy vs Nueva)

El sistema tiene **dos tablas de usuarios**:

1. **users** (legacy): Usada para autenticación
   - Tiene campos duplicados de bomberos (para compatibilidad)
   - role: super_admin, capitania, guardia, inventario, ayudante

2. **bomberos** (nueva): Datos oficiales del bombero
   - Es la fuente de verdad para datos personales
   - Se relaciona con users vía `mapa_bombero_usuario_legacy`

### Campos Nullable por Migración

Muchos campos `user_id` son nullable porque se están migrando a `firefighter_id`:
- `shift_users.user_id` → `shift_users.firefighter_id`
- `bed_assignments.user_id` → `bed_assignments.firefighter_id`
- `novelties.user_id` → `novelties.firefighter_id` (para academias)

### Índices Importantes

```sql
-- Bomberos por guardia y estado
INDEX ON bomberos(guardia_id, attendance_status)

-- Reemplazos activos
INDEX ON reemplazos_bomberos(bombero_titular_id, estado)
INDEX ON reemplazos_bomberos(bombero_reemplazante_id, estado)

-- Asignaciones de camas activas
-- (implícito: WHERE released_at IS NULL)
```

---

## Migraciones Clave

| Archivo | Propósito |
|---------|-----------|
| `2026_02_04_140000_create_firefighters_table.php` | Crea tabla bomberos |
| `2026_02_04_140020_create_firefighter_replacements_table.php` | Crea reemplazos_bomberos |
| `2026_02_04_140100_add_firefighter_ids_to_operational_tables.php` | Agrega firefighter_id a shift_users, bed_assignments, novelties |
| `2026_02_04_142000_make_shift_users_user_id_nullable.php` | Hace user_id nullable (migración a bomberos) |
| `2026_02_22_014709_add_guardia_id_and_is_permanent_to_novelties_table.php` | Agrega guardia_id e is_permanent a novelties |

---

## Diagrama de Herencia (Conceptual)

```
User (autenticación)
  │
  │ 1:1 (opcional, legacy)
  ▼
Bombero (datos personales)
  │
  │ N:M (vía shift_users)
  ▼
Shift (turno activo)
  │
  │ 1:N (vía bed_assignments)
  ▼
Bed (cama asignada)
```

---

*Documento generado el 23 Feb 2026*
*Sistema Guardia Nocturna v1.0*
