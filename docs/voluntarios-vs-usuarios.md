# Voluntarios (Bomberos) vs Usuarios con Login (roles)

## Resumen
En este sistema **NO existe una tabla separada para “Bomberos/Voluntarios”**.

Todo (voluntarios, jefes, cuentas institucionales de guardia y administradores) se guarda en la **misma tabla**: `users`.

La diferencia entre:
- **Personal operativo (voluntarios)**
- **Usuarios administrativos / cuentas con login**

se expresa principalmente por el campo **`role`** y algunos flags asociados (por ejemplo `guardia_id`, `is_titular`, `attendance_status`).

## Dónde se guardan
- **Tabla:** `users`
- **Modelo:** `App\Models\User`

Campos relevantes en `User`:
- `role`: define el tipo de usuario
- `guardia_id`: a qué guardia pertenece
- `is_titular`: titular/transitorio
- `attendance_status`: `constituye`, `reemplazo`, `permiso`, `ausente`, `falta`, `licencia`
- `job_replacement_id`: si está actuando como reemplazo de otro usuario
- `replacement_until`: vigencia del reemplazo

## Roles y qué significan
En la práctica, el sistema usa estos roles:

- `super_admin`
  - Usuario administrador del sistema.
  - Inicia sesión.

- `capitania`
  - Usuario de gestión / mando.
  - Inicia sesión.

- `guardia`
  - **Cuenta institucional** asociada a una guardia (ej: “Blitzkrieg”).
  - Inicia sesión.
  - Se usa para operar el **dashboard de guardia**.

## ¿Entonces los voluntarios están separados de los usuarios con login?
No. **Todos son `User`**.

Lo que sí ocurre es que:
- Para voluntarios creados desde la sección de “Voluntarios”, el sistema genera:
  - `password` aleatoria
  - `email` real si se entrega, o un placeholder `no-email-...@system.local`

Esto significa:
- Existen como registros en `users`.
- **Podrían** iniciar sesión si se les asigna un email válido y se les define/recupera una contraseña.

## Sección “Voluntarios”: cómo crea/lee datos
### Listado
Controlador: `App\Http\Controllers\VolunteerController@index`

Consulta base:
- toma usuarios con:
  - `role = 'bombero'` o `role = 'jefe_guardia'`

Es decir: para el sistema, “Voluntarios” = **usuarios operativos**.

### Crear voluntario
Controlador: `VolunteerController@store`

Acciones clave:
- valida datos (nombre, rut, guardia, etc.)
- fuerza:
  - `role = 'bombero'`
- setea:
  - `password` random
  - `email` placeholder si no viene

## Seeders: cómo se crean en datos de ejemplo
Seeder: `database/seeders/GuardiaSeeder.php`

Por cada guardia se crean 3 tipos de registros en `users`:
- **Cuenta institucional de guardia:** `role = guardia`
- **Jefe de guardia (persona):** `role = jefe_guardia`
- **Bomberos (personas):** `role = bombero`

## Asignación a guardia, titularidad y transitorios
- `guardia_id` define pertenencia.
- `is_titular`:
  - `true` normalmente = personal propio de la guardia
  - `false` normalmente = transitorio (reemplazo/canje)

La lógica de reemplazos usa:
- `job_replacement_id`
- `replacement_until`
- campos `original_*` para restaurar estado al expirar.

## Implicancia importante (diseño actual)
Como todo vive en `users`:
- reportes, asistencia y dotaciones operan siempre sobre `User`.
- **no hay “catálogo de personas” separado** de “cuentas con login”.

Si a futuro quisieras separar:
- una tabla `firefighters` (personas)
- y una tabla `users` (cuentas)

habría que migrar muchas referencias (`ShiftUser`, `Novelty.user_id`, reemplazos, etc.).
