# Manual de Desarrollador — GuardiaAPP / Guardia Nocturna

## 1. Visión general
Este documento describe la arquitectura lógica del sistema, módulos, validaciones, configuraciones, horarios operativos, puntos de extensión, y ubicación del código.

> Nota: Este repositorio es un proyecto Laravel (Blade + Controllers). La UI es mayormente Blade + Tailwind vía CDN.

---

## 2. Roles y permisos
Los permisos principales se manejan por `Auth::user()->role` en controladores y Blade.

Roles identificados:

- `guardia`
- `capitania`
- `super_admin`
- `inventario`

### 2.1 Reglas típicas
- Controladores suelen usar:
  - `abort(403, 'No autorizado.')`.
  - Check `in_array($role, [...], true)`.

Ubicación:

- `resources/views/layouts/app.blade.php` (renderizado condicional de menú)
- `app/Http/Controllers/*` (validaciones de permisos por endpoint)

---

## 3. Módulos principales

### 3.1 Perfil Guardia (Dashboard)
**Objetivo:** operación del turno desde el cuartel.

Archivos clave:

- `app/Http/Controllers/TableroController.php`
- `resources/views/dashboard.blade.php`
- `app/Http/Controllers/AdministradorController.php` (acciones de refuerzo/reemplazo/confirmación y guardado)

#### 3.1.1 Datos que arma el dashboard
En `TableroController@index` (cuenta `guardia`):

- Determina guardia de la cuenta (`$guardiaIdForGuardiaUser`).
- Expira reemplazos:
  - `ReplacementService::expire($now)`.
- Resuelve timezone:
  - `SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')))`.
- Prepara mapas de reemplazo activos:
  - `ReemplazoBombero::where('estado','activo')->where('guardia_id', $guardiaIdForGuardiaUser)->get()`
  - `replacementByOriginal = keyBy(bombero_titular_id)`
  - `replacementByReplacement = keyBy(bombero_reemplazante_id)`

Importante:

- El dashboard filtra `activeStaff` excluyendo titulares que estén siendo reemplazados.
- Para el reemplazante, el estado visual se fuerza a `reemplazo` si existe en `replacementByReplacement`.

#### 3.1.2 Auto-refresh / monitoreo
En `resources/views/dashboard.blade.php` existe un mecanismo de refresco suave:

- `softRefreshGuardiaDashboard()`
- `checkGuardiaUpdates()` que consulta `route('guardia.snapshot')`.

El snapshot se provee desde:

- `TableroController@guardiaSnapshot`

Campos clave:

- `latest_novelty_at`
- `latest_bombero_at`
- `latest_replacement_at`
- `attendance_saved_at`

Este sistema evita recargar si hay cambios locales sin guardar:

- `window.__attendanceDirty`

#### 3.1.3 Confirmación de asistencia (2 pasos)
Objetivo: exigir presencia física/validación por código antes de guardar asistencia para estados presentes.

Endpoint:

- `POST /admin/guardias/{guardia}/bomberos/{bombero}/confirm`
- Nombre de ruta: `admin.guardias.bomberos.confirm`

Controlador:

- `app/Http/Controllers/AdministradorController.php`
  - `confirmBombero()`
  - `makeAttendanceConfirmToken()`
  - `validateAttendanceConfirmToken()`

Flujo:

1. Frontend (dashboard) pide confirmación por AJAX con `numero_registro`.
2. Backend valida contra `Bombero.numero_registro`.
3. Backend retorna token firmado con timestamp.
4. Frontend guarda token en hidden input `users[id][confirm_token]`.
5. Al enviar “Guardar asistencia”, backend valida que todo presente tenga token válido.

Frontend:

- `resources/views/dashboard.blade.php`
  - `confirmBombero()`
  - `setConfirmState()` / `clearConfirmation()`
  - `refreshAttendanceSubmitButton()`

Validación server-side del guardado:

- `AdministradorController::bulkUpdateGuardia()`
  - valida ventana horaria
  - valida permisos
  - valida tokens para presentes

#### 3.1.4 Estados de asistencia y UI
Estados esperados:

- `constituye`
- `reemplazo`
- `permiso`
- `ausente`
- `licencia`
- `falta`

En UI:

- se usa un botón “cycle” para rotar estados.
- refuerzos y reemplazos bloquean la edición (si aplica).

#### 3.1.5 Refuerzos
Endpoints:

- Asignar refuerzo: `AdministradorController@assignRefuerzo`
- Quitar refuerzo: `AdministradorController@removeRefuerzo`

Reglas relevantes:

- Se guarda `refuerzo_guardia_anterior_id` para restauración.
- Se marca `es_refuerzo=true`.

#### 3.1.6 Reemplazos
Modelo:

- `ReemplazoBombero` (tabla `reemplazos_bomberos`)

Asignación:

- `AdministradorController@assignReplacement`
  - crea registro `ReemplazoBombero` con `estado=activo`, `inicio`, `fin`.
  - persiste en `notas` el `replacement_previous_guardia_id`.
  - setea `replacement.guardia_id = guardia.id`.
  - setea `original.estado_asistencia = ausente`.

Deshacer:

- `AdministradorController@undoReplacement`
  - cierra el reemplazo
  - restaura guardia/origen del reemplazante
  - limpia estado legacy en `users.job_replacement_id` si existe

Expiración:

- `app/Services/ReplacementService.php`
  - `calculateReplacementUntil()` (cálculo de término)
  - `expire()` (cierra los vencidos)

---

### 3.2 Guardia NOW (monitoreo en vivo)
**Objetivo:** permitir a capitanía/super admin monitorear en tiempo real lo que sucede en el cuartel.

Archivos:

- `app/Http/Controllers/GuardiaController.php`
  - `now()`
  - `nowData()`
- `resources/views/guardia_now.blade.php`

Características:

- Frontend hace polling cada 10s a `route('guardia.now.data')`.
- Backend retorna:
  - `server_time`
  - `shift` activo
  - lista de `bomberos` **solo en turno** (ShiftUser activos)

Reglas de scoping:

- Si `role === guardia`: se filtra el shift por la guardia del usuario.
- Si `role in (capitania, super_admin)`: se muestra global.

---

### 3.3 Voluntarios (Admin)
Controlador:

- `app/Http/Controllers/BomberoController.php`

Vista:

- `resources/views/admin/volunteers/index.blade.php`

Búsqueda server-side:

- parámetro GET `search`
- filtra por:
  - nombres/apellidos
  - rut
  - correo
  - cargo_texto
  - numero_portatil

Paginación:

- `paginate(20)->withQueryString()`
- view usa `appends(search)`

---

### 3.4 Navbar / Layout
Archivo:

- `resources/views/layouts/app.blade.php`

Puntos clave:

- Logo:
  - `public/brand/guardiapp.png`
  - tamaño controlado por clase Tailwind `h-14`

- Menú usuario super_admin:
  - dropdown toggled por click
  - IDs: `user-menu-root`, `user-menu-button`, `user-menu-dropdown`

---

### 3.5 QR / Planillas / Preventivas / Inventario
Archivos QR relevantes:

- Planillas:
  - `resources/views/admin/planillas/qr_fijo.blade.php`
  - `resources/views/admin/planillas/qr_fijo_print.blade.php`

- Inventario:
  - `resources/views/admin/inventario/qr.blade.php`
  - `resources/views/admin/inventario/qr_print.blade.php`

- Preventivas:
  - `resources/views/admin/preventivas/qr.blade.php`

Ajustes de tamaño:

- Print pages controlan tamaño del logo en CSS: `.brand img { height: ... }`.
- En vistas “screen”, el QR es SVG (`{!! $qrSvg !!}`) y crece según contenedor/padding.

---

## 4. Configuración del sistema (SystemSetting)
La aplicación usa settings persistidos para operar horarios y comportamiento.

Modelo:

- `App\Models\SystemSetting`

Usos relevantes:

- `guardia_schedule_tz`
- `guardia_daily_end_time` (default `07:00`)
- `attendance_enable_time` (default `21:00`)
- `attendance_disable_time` (default `10:00`)

---

## 5. Horarios e invariantes operativas

### 5.1 Fin de día guardia / limpieza
- Hora configurable: `guardia_daily_end_time` (default `07:00`).

Existen dos mecanismos:

1. Limpieza en carga de dashboard (`TableroController@index`) que libera no titulares después del end time.
2. Scheduler (`bootstrap/app.php`) para comando `guardia:daily-cleanup` ejecutado a `07:00` en timezone configurado.

### 5.2 Ventana habilitada para guardar asistencia
En `AdministradorController::bulkUpdateGuardia()` se valida ventana:

- habilita desde `attendance_enable_time` (default `21:00`)
- deshabilita en `attendance_disable_time` (default `10:00`)
- soporta ventana cruzando medianoche.

---

## 6. Ubicación de rutas
Archivo:

- `routes/web.php`

Rutas clave (nombres):

- `dashboard`
- `guardia.now`
- `guardia.now.data`
- `guardia.snapshot`
- `admin.volunteers.index`
- `admin.users.index`
- `admin.system.index`
- `admin.guardias.bulk_update`
- `admin.guardias.bomberos.confirm`

---

## 7. Cómo ajustar tamaños de logos (rápido)

### 7.1 Navbar
`resources/views/layouts/app.blade.php`

- clase actual: `h-14`
- para agrandar: `h-16`, `h-20`...

### 7.2 Login
`resources/views/auth/login.blade.php`

- clase actual: `h-[340px]`
- ajustar a `h-[380px]`, etc.

### 7.3 QR print (planillas/inventario)
Archivos:

- `resources/views/admin/planillas/qr_fijo_print.blade.php`
- `resources/views/admin/inventario/qr_print.blade.php`

Buscar:

- `.brand img { height: 86px; }`

---

## 8. Troubleshooting

### 8.1 NOW muestra gente fuera de turno
- Verificar `GuardiaController@nowData`:
  - debe mapear desde `Shift->users` con `end_time IS NULL`.

### 8.2 Confirmación no habilita Guardar
- Revisar:
  - inputs `users[id][confirm_token]` existan
  - endpoint confirmación retorna `ok=true`
  - `bulkUpdateGuardia` valida token

### 8.3 Reemplazos no se reflejan en dashboard
- Revisar scoping:
  - `TableroController@index`: `ReemplazoBombero::where('guardia_id', $guardiaIdForGuardiaUser)`
- Forzar visual reemplazo:
  - `dashboard.blade.php`: `$status = $repAsReplacement ? 'reemplazo' : ...`

---

Fin del manual.
