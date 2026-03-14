# Diagnóstico y Corrección del Dashboard Guardia

## PROBLEMA 1: Error 500 en Deshacer Reemplazo

### Qué estaba roto
- **Error:** `TypeError: Argument #2 ($replacement) must be of type App\Models\ReemplazoBombero, string given`
- **Causa raíz:** Laravel no podía hacer route model binding porque faltaba el método `getRouteKeyName()` en el modelo `ReemplazoBombero`
- **Archivo:** `app/Models/ReemplazoBombero.php`
- **Línea del error:** AdministradorController::undoReplacement() línea 593

### Qué se corrigió
- Agregado método `getRouteKeyName()` al modelo `ReemplazoBombero` que retorna 'id'
- Ahora Laravel puede resolver automáticamente el modelo desde el parámetro de ruta `{replacement}`

### Archivo modificado
```php
// app/Models/ReemplazoBombero.php
public function getRouteKeyName()
{
    return 'id';
}
```

---

## PROBLEMA 2: Imagen del Bombero Cortada y Mal Visualizada

### Qué estaba roto
- **Problema visual:** Imagen con `aspect-[4/3]` muy contenida, cortaba la cara
- **Información faltante:** Años de servicio y número radial no se mostraban
- **Diseño:** Información separada de la imagen, no aprovechaba el espacio

### Qué se corrigió
1. **Imagen agrandada:** De `aspect-[4/3]` a `h-48` (192px de altura fija)
2. **Object-fit mejorado:** De `object-top` a `object-center` para centrar mejor las caras
3. **Overlay con información:** Gradiente inferior con toda la info del bombero sobre la imagen
4. **Información completa visible:**
   - Nombre completo
   - Cargo
   - Años de servicio (con icono calendario)
   - Número radial/portatil (con icono radio)
5. **Badges reorganizados:** Jefe de guardia, conductor, rescate, trauma en esquina superior derecha

### Archivos modificados
- `resources/views/dashboard/partials/guardia/_staff_card.blade.php` - Rediseño completo del bloque de imagen
- `app/Models/Bombero.php` - Agregado accessor `getServiceLabelAttribute()`

---

## PROBLEMA 3: Confirmación por Código No Funciona

### Diagnóstico técnico completo

#### Backend (FUNCIONA CORRECTAMENTE)
**Ruta:** `/admin/guardias/{guardia}/bomberos/{bombero}/confirm` (POST)
**Controlador:** `AdministradorController::confirmBombero()`
**Validación:** Compara `$request->numero_registro` con `$bombero->numero_registro`
**Respuesta exitosa:** `{ ok: true, token: "...", ts: timestamp }`
**Respuesta error:** `{ ok: false, message: "Código inválido" }`

✅ **El backend funciona correctamente**

#### Frontend JavaScript (FUNCIONA CORRECTAMENTE)
**Archivo:** `resources/views/dashboard/_modals.blade.php`
**Función:** `window.confirmBombero(guardiaId, bomberoId)`
**Flujo:**
1. Obtiene código del input `confirm-code-{bomberoId}`
2. Valida que no esté vacío
3. **Valida `window.__draftEditable`** ← AQUÍ ESTÁ EL BLOQUEO
4. Hace POST a `/admin/guardias/${guardiaId}/bomberos/${bomberoId}/confirm`
5. Si OK: llama `setConfirmState()` y `__persistDraftConfirmation()`

✅ **El JavaScript funciona correctamente**

### QUÉ ESTABA BLOQUEANDO LA CONFIRMACIÓN

**BLOQUEO POR HORARIO:** El sistema solo permite confirmar entre las 22:00 y las 07:00.

```javascript
// Línea 1162 de _modals.blade.php
if (!window.__draftEditable) {
    // Muestra toast: "EDICIÓN BLOQUEADA FUERA DEL HORARIO 22:00 - 07:00."
    return;
}
```

La variable `__draftEditable` se determina en:
- `app/Services/TurnoDraftService.php::isEditableNow()`
- Verifica que la hora actual esté entre 22:00 y 07:00

**SOLUCIÓN PARA TESTING:**
Agregado soporte para `DRAFT_ALWAYS_EDITABLE=true` en `.env`

```php
// app/Services/TurnoDraftService.php
public function isEditableNow(?Carbon $now = null): bool
{
    // Para desarrollo/testing
    if (env('DRAFT_ALWAYS_EDITABLE', false)) {
        return true;
    }
    // ... validación de horario normal
}
```

---

## PROBLEMA 4: Cambio de Estado No Funciona

### Diagnóstico técnico completo

**MISMO BLOQUEO POR HORARIO**

```javascript
// Línea 971 de _modals.blade.php
window.setGuardiaStatus = function(userId, status) {
    if (!window.__draftEditable) {
        // Muestra toast: "EDICIÓN BLOQUEADA FUERA DEL HORARIO 22:00 - 07:00."
        return;
    }
    // ...
}
```

#### Flujo del cambio de estado (FUNCIONA CORRECTAMENTE)
1. Click en botón → `cycleGuardiaStatus(userId)`
2. Cicla estados: constituye → permiso → ausente → licencia → falta
3. Llama `setGuardiaStatus(userId, newStatus)`
4. **Valida `__draftEditable`** ← AQUÍ ESTÁ EL BLOQUEO
5. Actualiza input hidden
6. Limpia confirmación (porque cambió estado)
7. Actualiza UI del botón (`updateGuardiaCardUI`)
8. Persiste en draft (`__persistDraftItemStatus`)

✅ **El flujo funciona correctamente, solo bloqueado por horario**

---

## ARCHIVOS MODIFICADOS

| Archivo | Cambios |
|---------|---------|
| `app/Models/ReemplazoBombero.php` | Agregado `getRouteKeyName()` para route model binding |
| `app/Models/Bombero.php` | Agregado `getServiceLabelAttribute()` para años de servicio |
| `app/Services/TurnoDraftService.php` | Agregado soporte `DRAFT_ALWAYS_EDITABLE` para testing |
| `resources/views/dashboard/partials/guardia/_staff_card.blade.php` | Rediseño completo: imagen h-48, overlay con info, badges reorganizados |
| `.env.testing` | Archivo nuevo con instrucciones para testing sin horario |

---

## CÓMO PROBAR MANUALMENTE

### 1. Habilitar modo testing (eliminar bloqueo de horario)

Agregar en `.env`:
```
DRAFT_ALWAYS_EDITABLE=true
```

Ejecutar:
```bash
php artisan config:clear
```

### 2. Probar Confirmación por Código

1. Ir al dashboard guardia
2. Buscar una tarjeta con estado "Constituye"
3. En el campo "N° Registro" ingresar el `numero_registro` del bombero
4. Click en "OK"
5. **Resultado esperado:**
   - Desaparece el input y botón
   - Aparece "CONFIRMADO" con check verde
   - Borde de la tarjeta se pone verde (#34d399)
6. **Si el código es incorrecto:**
   - Toast: "CÓDIGO INVÁLIDO"

### 3. Probar Cambio de Estado

1. Click en el botón de estado (ej: "CONSTITUYE")
2. **Resultado esperado:**
   - Cambia a "PERMISO" (naranja)
   - Click de nuevo → "AUSENTE" (gris)
   - Click de nuevo → "LICENCIA" (azul)
   - Click de nuevo → "FALTA" (rojo, pide confirmación)
   - Click de nuevo → vuelve a "CONSTITUYE" (verde)
3. **Efecto secundario esperado:**
   - Al cambiar estado se limpia la confirmación previa

### 4. Probar Deshacer Reemplazo

1. En una tarjeta que muestre "Reemplaza a..." o "Reemplazado por..."
2. Click en "Deshacer"
3. Aparece modal de confirmación
4. Click en "Confirmar"
5. **Resultado esperado:**
   - NO debe aparecer error 500
   - Página recarga
   - El reemplazo ya no aparece

### 5. Verificar Información Visible en Tarjeta

Cada tarjeta debe mostrar:
- ✅ Apellido en header
- ✅ Foto grande (192px altura) sin cortar la cara
- ✅ Nombre completo sobre la imagen
- ✅ Cargo sobre la imagen
- ✅ Años de servicio (con icono calendario)
- ✅ Número radial si existe (con icono radio)
- ✅ Badges de especialidades (conductor, rescate, trauma)
- ✅ Badge de jefe de guardia si corresponde
- ✅ Badge de cama si está asignada

---

## RESUMEN EJECUTIVO

### Lo que NO estaba roto
- ✅ Backend de confirmación funciona correctamente
- ✅ Backend de cambio de estado funciona correctamente
- ✅ JavaScript de confirmación funciona correctamente
- ✅ JavaScript de cambio de estado funciona correctamente

### Lo que SÍ estaba roto
1. ❌ Deshacer reemplazo → Error 500 por falta de `getRouteKeyName()`
2. ❌ Imagen mal cortada y sin información completa
3. ❌ Años de servicio no se mostraban (faltaba accessor)

### Lo que estaba BLOQUEADO por diseño
- 🔒 Confirmación y cambio de estado solo funcionan entre 22:00 - 07:00
- 🔓 Ahora existe `DRAFT_ALWAYS_EDITABLE=true` para testing

### Estado final
- ✅ Todos los problemas funcionales corregidos
- ✅ Tarjeta rediseñada con imagen grande y info completa
- ✅ Sistema de testing implementado
- ✅ Listo para probar en cualquier horario
