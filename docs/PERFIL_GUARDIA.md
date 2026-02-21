# Perfil Guardia — Cómo Funciona

## ¿Qué es el Perfil Guardia?

El **Perfil Guardia** es la vista principal que usa el jefe de guardia (o quien esté a cargo) para gestionar el turno nocturno. Es una pantalla de control donde se ve quién está presente, quién faltó, quién llegó como refuerzo, y se registra la asistencia oficial del turno.

Esta vista es exclusiva para el rol `guardia`. No la ven los administradores ni los bomberos individuales.

---

## ¿Qué se ve en el Dashboard?

Al entrar al dashboard, el jefe de guardia ve:

### 1. Encabezado del Turno
- El nombre de su guardia (ej. "Guardia Alfa")
- Si está **EN TURNO** o **FUERA DE TURNO** según la semana activa
- La hora actual en tiempo real (reloj digital)
- Botones de acción rápida: pantalla completa, aseo, emergencias, agregar refuerzo, guardar asistencia, cerrar sesión

### 2. Tarjetas del Personal
El corazón del dashboard. Hay una tarjeta por cada bombero de la guardia. Cada tarjeta muestra:

- **Foto o inicial** del bombero
- **Nombre completo**
- **Cargo**: Jefe de Guardia o Bombero
- **Roles especiales**: Conductor, Operador de Rescate, Asistente de Trauma
- **Antigüedad** en años de servicio
- **Número de portátil**
- **Estado de asistencia actual**: constituye, permiso, ausente, licencia, falta
- **Badge especial** si es reemplazo o refuerzo
- **Borde verde** si ya fue confirmado para ese turno

### 3. Personal Inhabilitado
Al final del dashboard hay una sección separada con los bomberos marcados como "fuera de servicio". Desde ahí se pueden rehabilitar.

### 4. Resumen de Estados
Un contador en la parte superior muestra cuántos bomberos hay en cada estado: cuántos constituyen, cuántos están de permiso, cuántos ausentes, etc.

---

## El Ciclo de un Turno Nocturno

### Antes del Turno (desde las 21:00)
El sistema habilita la posibilidad de registrar asistencia desde las 21:00. Antes de esa hora, el botón de guardar está bloqueado.

### Al Iniciar el Turno
El jefe de guardia ve a todos sus titulares con estado "constituye" por defecto. Desde ahí puede:
- Cambiar el estado de alguien que faltó (ausente, permiso, licencia, falta)
- Agregar un reemplazo para quien no pudo venir
- Agregar un refuerzo si se necesita más personal

### Confirmación de Asistencia (2 pasos)
Para guardar la asistencia, **cada bombero presente debe ser confirmado individualmente**. Esto funciona así:

1. El jefe hace clic en la tarjeta del bombero
2. Aparece un campo para ingresar un código de confirmación
3. El jefe ingresa el código (es el código de la guardia, no del bombero)
4. La tarjeta se marca con borde verde: "CONFIRMADO"
5. Solo cuando **todos los presentes están confirmados**, se habilita el botón "Guardar Asistencia"

Esto evita que se guarde asistencia sin verificar quién realmente está presente.

### Guardar Asistencia
Al presionar "Guardar Asistencia":
- Se registra el estado de cada bombero en el historial
- Se crea un registro oficial del turno
- Aparece el badge "GUARDIA CONSTITUIDA" en el encabezado

---

## Los 3 Tipos de Personas en el Dashboard

### Titulares
Son los bomberos que pertenecen permanentemente a esa guardia. Siempre aparecen. No se van a las 07:00. Son la base del turno.

### Reemplazos
Cuando un titular no puede venir, otro bombero de otra guardia lo reemplaza. En el dashboard:
- El titular aparece marcado como "reemplazado" (no necesita confirmación)
- El reemplazante aparece con un badge "REEMPLAZO" en su tarjeta
- El jefe puede deshacer el reemplazo si es necesario

A las 07:00 AM, el reemplazo se limpia automáticamente. El reemplazante vuelve a su guardia original. El titular vuelve a aparecer normalmente al día siguiente.

### Refuerzos
Son bomberos que se suman al turno como personal adicional, sin reemplazar a nadie específico. En el dashboard:
- Aparecen con un badge "REFUERZO" en su tarjeta
- El jefe puede quitarlos con un botón "Quitar refuerzo"

A las 07:00 AM, el refuerzo también se limpia automáticamente. El bombero vuelve a su guardia original.

---

## Cómo se Agregan Reemplazos y Refuerzos

### Agregar un Reemplazo
En cada tarjeta de titular hay un botón "Reemplazar". Al presionarlo:
1. Se abre un modal con un buscador de voluntarios
2. El jefe busca al bombero que vendrá a reemplazar
3. Confirma la selección
4. El reemplazante aparece en el dashboard con el badge "REEMPLAZO"
5. El titular queda marcado como reemplazado y no necesita confirmación

### Agregar un Refuerzo
En el encabezado hay un botón "Refuerzo". Al presionarlo:
1. Se abre un modal con un buscador de voluntarios
2. El jefe busca al bombero que vendrá como refuerzo
3. Confirma la selección
4. El refuerzo aparece en el dashboard con el badge "REFUERZO"

---

## El Reset de las 07:00 AM

Todos los días a las 07:00 AM, el sistema limpia automáticamente:
- Los reemplazos activos (el reemplazante vuelve a su guardia)
- Los refuerzos activos (el refuerzo vuelve a su guardia)

**¿Por qué?** Porque el turno nocturno termina a las 07:00. El bombero que vino a reemplazar o reforzar cumplió su turno y queda libre.

**¿Pueden volver?** Sí. Esa misma noche (o cualquier otra), el mismo bombero puede volver a ser reemplazo o refuerzo. Es completamente libre y se puede hacer las veces que sea necesario.

**Importante**: el reset solo afecta a los bomberos del turno **anterior**. Si agregas un refuerzo a las 08:00 AM, ese refuerzo persiste hasta las 07:00 AM del día siguiente. No se borra al recargar la página.

---

## Estados de Asistencia

Cada bombero puede tener uno de estos estados:

| Estado | Significado | ¿Requiere confirmación? |
|--------|-------------|------------------------|
| **Constituye** | Asiste normalmente al turno | Sí |
| **Reemplazo** | Está reemplazando a un titular | Sí |
| **Refuerzo** | Está como personal adicional | Sí |
| **Permiso** | Tiene permiso autorizado | No |
| **Ausente** | No asistió | No |
| **Licencia** | Tiene licencia médica | No |
| **Falta** | Falta injustificada | No |
| **Inhabilitado** | Fuera de servicio temporal | No |

Los estados que requieren confirmación son los que indican presencia física en el turno.

---

## Validaciones del Sistema

El sistema tiene varias validaciones para evitar errores:

- **No se puede guardar asistencia** si hay bomberos presentes sin confirmar
- **No se puede agregar un refuerzo** si ese bombero ya está en un reemplazo activo
- **No se puede reemplazar** a alguien que ya tiene un reemplazo activo
- **No se puede guardar asistencia** fuera del horario habilitado (21:00 a 10:00)
- **No se puede gestionar** la guardia de otro (cada cuenta de guardia solo ve y gestiona la suya)

---

## Información Adicional en el Dashboard

Además del personal, el dashboard muestra:

- **Novedades recientes**: últimas novedades registradas en el sistema
- **Academias**: últimas actividades de academia
- **Cumpleaños**: bomberos que cumplen años próximamente o este mes
- **Camas disponibles**: cuántas camas hay ocupadas y disponibles en el cuartel

---

## Flujo Completo de un Turno

```
21:00 → Se habilita el registro de asistencia
   ↓
El jefe ve a todos sus titulares
   ↓
Gestiona ausencias → agrega reemplazos si es necesario
   ↓
Agrega refuerzos si se necesita más personal
   ↓
Confirma uno a uno a los bomberos presentes (código)
   ↓
Todos confirmados → se habilita "Guardar Asistencia"
   ↓
Guarda → aparece "GUARDIA CONSTITUIDA"
   ↓
07:00 AM → Se limpian reemplazos y refuerzos del turno anterior
   ↓
El ciclo se repite la noche siguiente
```

---
