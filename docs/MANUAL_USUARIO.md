# Manual de Usuario — GuardiaAPP / Guardia Nocturna

## 1. Objetivo del sistema
Este sistema permite gestionar la operación diaria de un cuartel / compañía:

- Guardia (turnos) y dotación.
- Asistencia y estados del personal.
- Reemplazos y refuerzos.
- Bitácora de novedades.
- Camas (disponibilidad/ocupación).
- Academias (registro y seguimiento).
- Módulos administrativos (voluntarios, usuarios, configuración de sistema, etc.).

> Este manual está orientado a usuarios finales (operadores). Para detalles técnicos y configuración, ver el **Manual de Desarrollador**.

---

## 2. Perfiles / Roles
El sistema utiliza roles (perfiles) para habilitar menús y permisos.

### 2.1 Guardia (cuenta de guardia)
Pensado para el uso en el cuartel durante el turno.

Funciones principales:

- Ver el **Perfil Guardia** (panel principal con tarjetas).
- Marcar/gestionar asistencia (ventana horaria).
- Confirmación de asistencia (2 pasos con código).
- Asignar/quitar refuerzos.
- Asignar/deshacer reemplazos.
- Registrar novedades.
- Registrar academias.
- Ver estado de camas.

### 2.2 Capitanía
Pensado para supervisión.

Funciones principales:

- Acceso a **Guardia NOW** para monitoreo en vivo.
- Acceso a reportes y vistas de control (según configuración de menús).

### 2.3 Super Admin
Administración completa.

Funciones principales:

- Gestión de voluntarios.
- Gestión de usuarios.
- Administración del sistema (parámetros y configuraciones).
- Gestión completa de guardias, dotaciones y módulos.

### 2.4 Inventario
Perfil dedicado al flujo de inventario (retiros/QR/configuración).

---

## 3. Inicio de sesión
1. En la pantalla de login, ingresa:
   - **Usuario**
   - **Contraseña**
2. Presiona **Iniciar sesión**.

Si olvidas credenciales, debes solicitar asistencia al administrador del sistema.

---

## 4. Menú principal
Según tu rol verás opciones distintas.

- **Guardia NOW**: monitoreo en vivo.
- **Camas**: ver disponibilidad.
- **Mi Dotación** / **Dotaciones**: gestión de personal por guardia.
- **Preventivas** / **Planillas**: módulos operativos.
- **Inventario**: módulo de bodega.

En el perfil **super_admin**, opciones de administración aparecen en el menú del usuario.

---

## 5. Perfil Guardia (panel principal de guardia)
Este es el “tablero” operativo que se usa durante el turno.

### 5.1 Tarjetas de bomberos
Cada bombero se muestra en una tarjeta con:

- Nombre/apellido.
- Foto (si existe).
- Cargo/rol.
- Portátil.
- Antigüedad.
- Especialidades (ej. Conductor, Rescate, AT.M).
- Etiquetas especiales:
  - **REFUERZO** (si está agregado como refuerzo).
  - **REEMPLAZO** (si está actuando como reemplazante).

### 5.2 Estados de asistencia
Los estados principales son:

- **CONSTITUYE**
- **PERMISO**
- **AUSENTE**
- **LICENCIA**
- **FALTA**
- **REEMPLAZO** (cuando el bombero está reemplazando a otro)

El estado se cambia con un **botón único** que va ciclando por los estados (para ahorrar espacio y facilitar operación rápida).

### 5.3 Confirmación de asistencia (2 pasos)
Para guardar asistencia, el sistema exige confirmación individual por código en los casos que correspondan (por ejemplo: presentes).

Flujo:

1. En la tarjeta del bombero, en el bloque **Confirmación**, ingresa el **código** (número de registro).
2. Presiona **Confirmar**.
3. El sistema marcará la tarjeta como confirmada.
4. Cuando todos los bomberos requeridos estén confirmados, se habilita el botón **Guardar asistencia**.

Importante:

- Si cambias el **estado** de un bombero, su confirmación puede invalidarse (debes confirmar nuevamente si aplica).
- Si el código es incorrecto, el sistema rechazará la confirmación.

### 5.4 Refuerzos
Un refuerzo es un bombero agregado temporalmente a la guardia.

- **Agregar refuerzo**: se selecciona un voluntario y se agrega a la guardia.
- **Quitar refuerzo**: en la tarjeta del refuerzo, usar la opción para retirarlo.

### 5.5 Reemplazos
Un reemplazo permite que un bombero cubra el turno de otro.

- **Asignar reemplazo**: en la tarjeta del bombero original, elegir “Reemplazar” y seleccionar reemplazante.
- **Deshacer reemplazo**: en la tarjeta del reemplazante aparece “Reemplaza a …” y el botón “Deshacer reemplazo”.

### 5.6 Novedades
La bitácora permite registrar eventos relevantes del turno.

- Registrar una novedad.
- Ver últimas novedades.

### 5.7 Academias
Permite registrar academias nocturnas.

- Registrar academia.
- Revisar las últimas registradas.

### 5.8 Camas
Muestra el estado general de camas.

- Disponibles / ocupadas.

---

## 6. Guardia NOW (monitoreo en vivo)
Esta vista está orientada a la supervisión en tiempo real.

Características:

- Se actualiza automáticamente (polling).
- Muestra **solo bomberos en turno** (turno activo) con tarjetas pequeñas.
- Refleja el estado y banderas (jefe, refuerzo, sanción, etc.)

---

## 7. Gestión de Voluntarios (Administración)
Disponible para perfiles autorizados (super_admin / capitanía según permisos).

### 7.1 Buscar voluntarios
El buscador funciona sobre toda la base de datos. Puedes buscar por:

- Nombres
- Apellidos
- RUT
- Correo
- Cargo
- Portátil

### 7.2 Crear/Editar voluntarios
Permite:

- Datos personales.
- Guardia asociada.
- Foto.
- Especialidades.

---

## 8. Usuarios (Administración)
Gestiona credenciales y roles.

- Crear/editar usuarios.
- Asignar roles.
- Restringir accesos.

---

## 9. Inventario (módulo de bodega)
Incluye:

- QR fijo para retiro.
- Flujo de identificación.
- Registro de retiros.

---

## 10. Recomendaciones operativas
- Mantén el panel de guardia en pantalla completa durante el turno.
- Confirma el código de cada presente antes de guardar.
- Usa Guardia NOW para monitoreo sin intervenir.

---

## 11. Preguntas frecuentes
### 11.1 ¿Por qué no puedo guardar asistencia?
- Estás fuera de la ventana horaria habilitada.
- Faltan confirmaciones por código.

### 11.2 ¿Por qué un bombero aparece como REEMPLAZO?
- Está cubriendo a otro bombero y existe un reemplazo activo.

### 11.3 ¿Por qué un bombero no aparece?
- Puede estar fuera de servicio.
- Puede estar reemplazado (en ese caso, se muestra el reemplazante).

---

Fin del manual.
