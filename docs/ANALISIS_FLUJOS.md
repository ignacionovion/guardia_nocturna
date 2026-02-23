# Análisis de Flujos - Guardia Nocturna

## Resumen Ejecutivo

Este documento analiza los tres flujos principales del sistema:
1. **Perfil Guardia Nocturna** (dashboard.blade.php)
2. **Camas** (camas.blade.php)
3. **NOW** (guardia_now.blade.php)

---

## 1. PERFIL GUARDIA NOCTURNA

### Archivo: `resources/views/dashboard.blade.php`

### ¿Qué hace?

Esta es la vista principal para usuarios con rol **"guardia"**. Es un dashboard fullscreen de tarjetas para gestionar la asistencia de bomberos.

#### Funcionalidades principales:

1. **Gestión de Asistencia (Sistema de Confirmación)**
   - Muestra tarjetas de cada bombero activo
   - Estados posibles: `constituye`, `reemplazo`, `permiso`, `ausente`, `licencia`, `falta`
   - **Sistema de confirmación de 2 pasos**: 
     - Los estados `constituye`, `reemplazo`, y `refuerzo` requieren confirmación con código
     - Los demás estados (`permiso`, `ausente`, `licencia`, `falta`) no requieren confirmación
   - Persistencia en `sessionStorage` (sobrevive refresh)
   - Borde verde en tarjetas cuando está confirmado

2. **Reemplazos**
   - Botón "Reemplazar" para asignar un bombero como reemplazo de otro
   - Muestra información de quién reemplaza a quién
   - Opción para deshacer reemplazo

3. **Refuerzos**
   - Modal para agregar bomberos de refuerzo
   - Se liberan automáticamente a las 07:00 AM del día siguiente
   - Botón "Quitar refuerzo"

4. **Inhabilitados**
   - Sección separada para bomberos fuera de servicio
   - Botones para inhabilitar/habilitar bomberos

5. **Registro de Novedades y Academias**
   - Modal para registrar novedades (con tipo "Permanente" visible para todas las guardias)
   - Modal para registrar academias nocturnas
   - Tipos: Informativa, Incidente, Mantención, Urgente, Permanente

6. **Reloj Digital**
   - Hora local en tiempo real
   - Indicador de conexión "EN LÍNEA"

7. **Próximos Cumpleaños**
   - Lista de cumpleaños del mes

8. **Estado de Camas**
   - Muestra camas disponibles/total

### Flujo de Confirmación de Asistencia:

```
1. Guardia cambia estado de bombero a "constituye"/"reemplazo"/refuerzo
2. Aparece caja de confirmación con input para código
3. Guardia ingresa código del bombero
4. JS valida código vía AJAX (ruta no mostrada en código visto)
5. Si código correcto:
   - Tarjeta se marca como confirmada (borde verde)
   - Se guarda en sessionStorage
   - Botón "Guardar asistencia" se habilita
6. Si todos los presentes están confirmados → se puede guardar asistencia
```

### Problemas detectados:

1. **⚠️ Mensaje de error no centrado**: Cuando el código es erróneo, el mensaje no aparece como toast centrado
2. **⚠️ Texto duplicado "CONFIRMADO"**: Hay texto redundante en la UI
3. **⚠️ Lógica de confirmación**: Solo `constituye`, `reemplazo`, y `refuerzo` deberían requerir confirmación (actualmente parece correcto)

### Archivos relacionados:
- `app/Http/Controllers/TableroController.php` - Controlador del dashboard
- `resources/views/dashboard.blade.php` - Vista principal
- JS de confirmación está inline en la vista (largo)

---

## 2. CAMAS

### Archivo: `resources/views/camas.blade.php`

### ¿Qué hace?

Sistema de gestión de camas/dormitorios para los bomberos. Controla ocupación en tiempo real.

#### Funcionalidades principales:

1. **Grid de Camas**
   - Muestra todas las camas en cards modernas
   - Estados: `available` (disponible), `occupied` (ocupada), `maintenance` (mantención)
   - Diseño con gradientes según estado (verde=disponible, rojo=ocupada, gris=mantención)

2. **Información mostrada por cama:**
   - Número de cama
   - Estado con badges
   - Si está ocupada:
     - Foto/iniciales del ocupante
     - Nombre completo
     - Cargo
     - Hora de ingreso
     - Tiempo transcurrido (diffForHumans)
     - Notas (si existen)

3. **Acciones por estado:**
   - **Disponible**: 
     - "Asignar" (abre modal)
     - "Mantención" (solo super_admin)
   - **Ocupada**:
     - "Liberar" (libera la cama)
   - **Mantención**:
     - "Habilitar" (solo super_admin)

4. **Modal de Asignación**
   - Selección de voluntario (dropdown)
   - Campo de comentario/nota opcional
   - Botón Confirmar/Cancelar

### Flujo de Asignación:

```
1. Usuario hace clic en "Asignar" en cama disponible
2. Se abre modal con lista de bomberos
3. Selecciona bombero y opcionalmente agrega nota
4. Submit al controller AsignacionCamaController
5. Se crea BedAssignment con assigned_at = now()
6. Se actualiza estado de cama a 'occupied'
```

### Flujo de Liberación:

```
1. Usuario hace clic en "Liberar" en cama ocupada
2. Confirmación vía JavaScript
3. Submit al controller
4. Se actualiza released_at = now()
5. Se actualiza estado de cama a 'available'
```

### Problemas detectados:

1. **⚠️ Timezone en assigned_at**: Se corrigió en checkpoint anterior (se agregó `assigned_at => now()` en el controller)

### Archivos relacionados:
- `app/Http/Controllers/AsignacionCamaController.php`
- `app/Models/BedAssignment.php`
- `resources/views/camas.blade.php`

---

## 3. NOW (Guardia NOW)

### Archivo: `resources/views/guardia_now.blade.php`

### ¿Qué hace?

Vista en vivo del estado de la guardia constituida. Es una vista de solo lectura (monitoring) que se actualiza automáticamente.

#### Funcionalidades principales:

1. **Polling Automático**
   - Actualiza cada 10 segundos via AJAX
   - Indicador visual de conexión (punto verde/rojo)
   - Timestamp de última actualización

2. **Información del Turno**
   - Estado (constituida/no constituida)
   - Jefe de guardia
   - Hora del servidor

3. **Grid de Bomberos**
   - Tarjetas con información de cada bombero
   - Estados visuales con colores:
     - `constituye`: verde
     - `reemplazo`: púrpura
     - `permiso`: ámbar
     - `ausente`: gris
     - `licencia`: azul
     - `falta`: rojo

4. **Badges/Medallas mostrados:**
   - **EN TURNO / PENDIENTE**: Indica si está confirmado en el turno
   - **JEFE**: Si es jefe de guardia
   - **REFUERZO**: Si es refuerzo
   - **CAMBIO**: Si es cambio
   - **SANCIÓN**: Si tiene sanción
   - **FUERA SERVICIO**: Si está fuera de servicio
   - **PERMANENTE**: Si es bombero permanente
   - Especialidades: COND (conductor), R (rescate), A.T (asistente trauma)

5. **Información adicional:**
   - Años/meses de servicio
   - Número móvil
   - Información de reemplazo (quién reemplaza a quién)

### Flujo de Datos:

```
1. Página carga inicialmente sin datos
2. JS hace fetch a route('guardia.now.data') cada 10 segundos
3. Controller GuardiaController::nowData() retorna JSON
4. JS renderiza las tarjetas dinámicamente
5. Si hay error de conexión, punto cambia a rojo
```

### Diferencias clave con Dashboard:

| Aspecto | Dashboard (Guardia) | NOW |
|---------|-------------------|-----|
| Propósito | Gestión editable | Solo lectura/monitoring |
| Interacción | Alta (cambia estados, confirma) | Baja (solo visualización) |
| Actualización | Manual (guardar asistencia) | Automática (10 seg polling) |
| Usuarios | Solo guardia | Todos los roles |
| Confirmación | Requiere código | Muestra badge "EN TURNO" |

### Problemas detectados:

1. **⚠️ Sin problemas obvios** en el código revisado

### Archivos relacionados:
- `app/Http/Controllers/GuardiaController.php` - Métodos `now()` y `nowData()`
- `resources/views/guardia_now.blade.php` - Vista

---

## Problemas Generales Encontrados

### 1. Migraciones
**Estado**: ✅ Corregido en commit reciente
- La migración `2026_02_22_014709_add_guardia_id_and_is_permanent_to_novelties_table.php` intentaba agregar columnas que ya existían
- Se agregaron checks `Schema::hasColumn()` para evitar errores en producción

### 2. Sistema de Confirmación (Dashboard)
**Estado**: ⚠️ Funcional pero con mejoras pendientes
- Checkpoint indica que funciona bien pero faltan ajustes mínimos:
  - Mensaje de error centrado/toast cuando código es erróneo
  - Eliminar texto duplicado "CONFIRMADO"

### 3. Timezone
**Estado**: ✅ Corregido
- Se detectó desfase de 3 horas en timestamps de camas
- Se corrigió agregando `assigned_at => now()` explícitamente

---

## Mapa de Relaciones

```
GuardiaController
├── index() → Carga guardia.blade.php (NO revisado en detalle)
├── now() → Carga guardia_now.blade.php
├── nowData() → JSON para polling de NOW
├── start() → Inicia turno
├── close() → Cierra turno
└── cleanupTransitoriosOnConstitution() → Limpia transitorios

TableroController (asumiendo)
└── index() → Carga dashboard.blade.php (vista guardia)

AsignacionCamaController
├── index() → Carga camas.blade.php
├── assign() → Asigna cama
└── release() → Libera cama
```

---

## Recomendaciones

1. **Dashboard - Sistema de Confirmación**:
   - Implementar toast/notificación centrada para errores de código
   - Limpiar texto duplicado "CONFIRMADO" de la UI
   - Verificar que solo constituye/reemplazo/refuerzo requieren confirmación

2. **NOW**:
   - Considerar agregar botón de "refresh manual" además del polling automático
   - Agregar sonido/visual cuando cambia el estado de un bombero

3. **Camas**:
   - Agregar historial de asignaciones (quién ocupó la cama y cuándo)
   - Notificación cuando se libera una cama

4. **General**:
   - Agregar logs de auditoría para cambios de estado
   - Implementar tests automatizados para los flujos críticos
