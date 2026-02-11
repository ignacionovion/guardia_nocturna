# Guardia Nocturna — Documentación general

## Propósito
Aplicación web para gestionar la operación de guardias: dotación (personal operativo), asistencia/constitución de guardia, reemplazos, aseo, novedades, turnos y emergencias.

## Roles de acceso (usuarios del sistema)
- super_admin
- capitania
- guardia

Nota: El personal operativo (bomberos) se gestiona como entidad separada y **no requiere login**.

## Módulos principales

### Dashboard de Guardia
- Visualiza la dotación asociada a la guardia.
- Permite registrar asistencia (estado de asistencia por persona).
- Permite asignar reemplazos y deshacerlos.
- Accesos rápidos (por ejemplo, asignación de aseo).

### Administración de Guardias
- Gestión de dotación por guardia.
- Registro/ajuste de asistencia.
- Gestión de reemplazos.

### Turnos
- Registro de turnos (shifts) y asignaciones por turno.
- Registro de responsables (p.ej. jefe de guardia / encargado de turno).

### Aseo
- Catálogo de tareas de aseo.
- Asignación de tareas a personal y seguimiento de estado.

### Novedades
- Registro de novedades (incidencias, avisos, comunicaciones internas) asociadas a personas.

### Emergencias
- Registro de emergencias con clave, unidades involucradas y datos operativos.
- Asociación a guardia y turno cuando corresponde.

### Recordatorios y notificaciones
- Recordatorios internos.
- Notificaciones in-app.

## Entidades funcionales (conceptuales)
- Guardia: unidad organizativa/turno de guardia (p.ej. Guardia 1, Guardia 2).
- Bombero (personal operativo): integrante de una guardia; se usa para dotación/asistencia/reemplazos.
- Usuario del sistema: quien inicia sesión para administrar/registrar información.

## Flujo de reemplazo (alto nivel)
- Se selecciona un titular a reemplazar y un reemplazante.
- Se crea un registro de reemplazo activo.
- Se actualiza la dotación visible: el titular reemplazado deja de mostrarse y aparece el reemplazante con referencia a quién reemplaza.
- Al deshacer/cerrar el reemplazo se restaura la dotación titular.

## Flujos de datos relevantes
- Asistencia: se registra por guardia (y opcionalmente por turno activo) y se refleja en estados de asistencia.
- Reemplazos: persisten un histórico (activos/cerrados) y afectan la dotación operativa.

## Rutas y controladores (orientativo)
- Dashboard: controlador de dashboard (carga dotación, reemplazos y estados).
- Administración: controlador admin (acciones sobre guardias, dotación, asistencia y reemplazos).
- Reportes: controlador de reportes (consultas agregadas, métricas).

## Convenciones de estado
- Estados de asistencia (ejemplos): constituye, permiso, ausente, falta, licencia.
- Reemplazos: active / closed.
