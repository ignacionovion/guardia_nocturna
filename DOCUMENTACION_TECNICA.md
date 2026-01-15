# Documentación Técnica - Sistema Guardia Nocturna

## Visión General
Este sistema está diseñado para la gestión integral de una Guardia Nocturna, desarrollado en **Laravel 11** con base de datos **MySQL**. El sistema actual funciona como una API RESTful que provee servicios para gestionar camas, personal, aseo, novedades y constitución de guardia.

## 1. Estructura de Base de Datos

### Usuarios (`users`)
- Gestión de autenticación y perfiles.
- Campos adicionales: `birthdate` (Fecha de nacimiento) para módulo de cumpleaños.

### Camas (`beds` y `bed_assignments`)
- **beds**: Catálogo de camas (Número, Estado: disponible/ocupada/mantención).
- **bed_assignments**: Historial y estado actual de asignaciones. Relaciona una Cama con un Usuario.
  - Campos: `assigned_at` (fecha asignación), `released_at` (fecha liberación), `notes`.

### Eventos de Personal (`staff_events`)
- Gestiona situaciones administrativas del personal.
- **Tipos**: Remplazos, Permisos, Licencias, Academias.
- Relaciones: Usuario principal y Usuario reemplazante (opcional).
- Estado: Pendiente, Aprobado, Rechazado.

### Aseo (`cleaning_tasks` y `cleaning_assignments`)
- **cleaning_tasks**: Catálogo de tareas (ej: "Limpiar Baños", "Ordenar Cocina").
- **cleaning_assignments**: Asignación diaria de tareas a usuarios.
  - Estado: Pendiente, Completado.

### Novedades (`novelties`)
- Bitácora de sucesos ocurridos durante la guardia o anuncios generales.
- Campos: Título, Descripción, Fecha, Tipo.

### Recordatorios (`reminders`)
- Sistema de alertas para fechas importantes.
- Tipos: General, Aniversario.
- Muestra recordatorios futuros ordenados por fecha.

### Guardia (`shifts` y `shift_users`)
- **shifts**: Representa un turno de guardia específico (Fecha, Jefe de Guardia, Estado).
- **shift_users**: Detalle del personal que constituye esa guardia.
  - Roles: Conductor, Paramédico, Cuartelero, etc.
  - Asistencia: Presente/Ausente.

## 2. API Endpoints (Rutas)

Todas las rutas están protegidas y requieren autenticación (Laravel Sanctum). Prefijo: `/api`.

### Autenticación & Usuarios
- `GET /api/user`: Obtener usuario autenticado.
- `GET /api/users`: Listar todos los usuarios.
- `GET /api/users/birthdays`: Listar usuarios que están de cumpleaños este mes.
- `GET /api/users/{id}`: Ver detalle de usuario con sus asignaciones actuales.

### Gestión de Camas
- `GET /api/beds`: Listar camas y su estado actual.
- `POST /api/beds`: Crear nueva cama.
- `POST /api/bed-assignments`: Asignar una cama a un usuario.
  - Requiere: `bed_id`, `user_id`.
  - Valida que la cama esté 'available'.
- `PUT /api/bed-assignments/{id}`: Actualizar asignación.
  - Enviar `release: true` para liberar la cama y marcar hora de salida.

### Personal
- `GET /api/staff-events`: Listar eventos (permisos, licencias).
- `POST /api/staff-events`: Registrar nuevo evento.

### Aseo
- `GET /api/cleaning-tasks`: Ver tareas disponibles.
- `POST /api/cleaning-assignments`: Asignar tarea a usuario.
- `PUT /api/cleaning-assignments/{id}`: Marcar tarea como completada.

### Guardia (Constituir)
- `GET /api/shifts`: Ver historial de guardias.
- `POST /api/shifts`: Crear/Abrir nueva guardia.
- `POST /api/shifts/{id}/users`: Agregar personal a la guardia.
  - Requiere: `user_id`, `role` (opcional).

### Novedades y Recordatorios
- `GET /api/novelties`: Ver novedades recientes.
- `GET /api/reminders`: Ver recordatorios activos/futuros.

## 3. Instalación y Ejecución

### Requisitos Previos
- PHP 8.2+
- Composer
- MySQL

### Pasos de Instalación
1. Clonar repositorio o acceder a la carpeta.
2. Instalar dependencias:
   ```bash
   composer install
   ```
3. Configurar entorno (`.env`):
   - Configurar credenciales de base de datos (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
4. Ejecutar migraciones y seeders (datos de prueba):
   ```bash
   php artisan migrate --seed
   ```
   *Esto creará 10 camas, tareas de aseo y usuarios de prueba.*

### Ejecutar Servidor
Para levantar el sistema en entorno local:

```bash
php artisan serve
```

El sistema estará accesible en: `http://127.0.0.1:8000`
