# Análisis Completo: Perfil Guardia - Sistema Guardia Nocturna

## Fecha de Análisis
Febrero 2025

## 1. Resumen Ejecutivo

El **Perfil Guardia** es el panel de control principal para cuentas con rol `guardia` en el sistema Guardia Nocturna. Este perfil permite gestionar el personal de una guardia específica, registrar asistencia, asignar reemplazos, agregar refuerzos y monitorear el estado operativo en tiempo real.

---

## 2. Arquitectura Técnica

### 2.1 Controlador Principal
**Archivo**: `app/Http/Controllers/TableroController.php`

#### Método `index()` - Lógica Principal

El dashboard de guardia se carga cuando `$user->role === 'guardia'`:

```php
// Identificación de la guardia asignada
$guardiaIdForGuardiaUser = $user->guardia_id;
if (!$guardiaIdForGuardiaUser) {
    // Fallback: buscar por nombre de usuario
    $guardiaIdForGuardiaUser = Guardia::whereRaw('lower(name) = ?', [strtolower($user->name)])->value('id');
}
if (!$guardiaIdForGuardiaUser) {
    // Fallback: buscar por email
    $emailLocal = explode('@', (string) $user->email)[0] ?? '';
    $guardiaIdForGuardiaUser = Guardia::whereRaw('lower(name) = ?', [strtolower($emailLocal)])->value('id');
}
```

#### Auto-reset de Personal No-Titular (Líneas 183-204)

**Lógica Crítica**: Si la hora local es >= 07:00, se resetea automáticamente:
- Refuerzos vuelven a su guardia original
- Cambios y sanciones se limpian
- Esto explica por qué Muñoz desapareció después de agregarse

```php
if ($localNow->greaterThanOrEqualTo($endAt)) {
    Bombero::query()
        ->where('guardia_id', $guardiaIdForGuardiaUser)
        ->where('es_titular', false)
        ->get()
        ->each(function (Bombero $b) {
            $restoreGuardiaId = null;
            if ((bool) ($b->es_refuerzo ?? false)) {
                $restoreGuardiaId = $b->refuerzo_guardia_anterior_id;
            }
            $b->update([
                'guardia_id' => $restoreGuardiaId,
                'es_refuerzo' => false,
                'refuerzo_guardia_anterior_id' => null,
            ]);
        });
}
```

### 2.2 Carga de Personal

**Líneas 275-278**:
```php
$myStaff = Bombero::where('guardia_id', $guardiaIdForGuardiaUser)
    ->orderBy('apellido_paterno')
    ->orderBy('nombres')
    ->get();
```

**Problema Identificado**: El personal se carga en el orden de apellido, pero el dashboard lo reordena poniendo reemplazos y refuerzos al final.

### 2.3 Sistema de Reemplazos

**Tabla**: `reemplazos_bomberos`
**Modelo**: `ReemplazoBombero`

**Lógica**:
- `replacementByOriginal`: Mapeo de bombero original → registro de reemplazo
- `replacementByReplacement`: Mapeo de bombero reemplazante → registro
- Se usa para bloquear el cambio de estado de reemplazantes

**Candidatos a Reemplazo** (Líneas 280-300):
```php
$replacementCandidates = Bombero::query()
    ->where(function ($q) use ($guardiaIdForGuardiaUser) {
        $q->whereNull('guardia_id')
            ->orWhere('guardia_id', '!=', $guardiaIdForGuardiaUser);
    })
    ->whereNotIn('id', $activeReplacements->pluck('bombero_reemplazante_id'))
    ->get();
```

---

## 3. Vista del Dashboard

**Archivo**: `resources/views/dashboard.blade.php`

### 3.1 Estructura de Personal Activo

```php
// Filtrado y ordenamiento (Líneas 40-55)
$activeStaff = $myStaff
    ->reject(fn($u) => $u->fuera_de_servicio)
    ->sortBy(fn($u) => sprintf('%d-%s-%s', 
        ($isReplacement || $isRefuerzo) ? 1 : 0,  // Prioridad: 0=normal, 1=extra
        $u->apellido_paterno, 
        $u->nombres
    ));
```

### 3.2 Tarjeta de Bombero

**Altura fija**: `h-[420px]`
**Grid**: `grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6`

**Estructura de cada tarjeta**:
1. Header con nombre y botón inhabilitar
2. Foto + Info personal (cargo, antigüedad, móvil)
3. Badge de REFUERZO (si aplica)
4. Sección de reemplazo (si aplica)
5. Caja de confirmación (para constituye/reemplazo/refuerzo)
6. Botón de estado (ciclo: CONSTITUYE → PERMISO → AUSENTE → LICENCIA → FALTA)
7. Botón REEMPLAZAR (solo si no es refuerzo ni reemplazo)
8. Botón Quitar refuerzo (solo si es refuerzo)

### 3.3 Sistema de Confirmación

**Lógica** (basada en checkpoint confirmado):
- Estados que requieren confirmación: `constituye`, `reemplazo`, `refuerzo`
- Estados que NO requieren: `permiso`, `ausente`, `licencia`, `falta`, `inhabilitado`
- Confirmación de 2 pasos con código numérico
- Persistencia en `sessionStorage`
- Borde verde cuando está confirmado

---

## 4. Problemas Identificados

### 4.1 Refuerzo No Aparece (CRÍTICO)

**Causa Raíz**: El auto-reset a las 07:00 remueve a los refuerzos.

**Flujo del problema**:
1. Usuario agrega refuerzo (Muñoz) → Éxito
2. Dashboard hace soft-refresh (AJAX)
3. Pero al recargar la página, el controlador ejecuta la lógica de reset
4. Muñoz vuelve a su guardia original (guardia_id cambia)
5. Ya no aparece en `$myStaff`

**Fix Propuesto**:
- Opción A: Deshabilitar auto-reset temporalmente
- Opción B: Agregar lógica de "horario de corte" configurable
- Opción C: Marcar refuerzos agregados después de las 07:00 como "permanentes del día"

### 4.2 Select de Reemplazo Feo

**Problema**: Uso de `<datalist>` con estilo por defecto del navegador.

**Actual**:
```html
<input list="replacement-candidates" ...>
<datalist id="replacement-candidates">
    <option value="Nombre - RUT">
</datalist>
```

**Fix**: Reemplazar con componente custom tipo "combobox" o usar Select2/Tom Select.

### 4.3 Layout de Tarjetas Se Rompe

**Causa**: Las tarjetas tienen altura fija (`h-[420px]`) pero el contenido variable.

Cuando hay reemplazo, se agrega la sección de reemplazo (líneas 279-291) que ocupa espacio extra, empujando todo hacia abajo y creando desalineación.

**Fix**: 
- Usar `min-h` en lugar de `h` fijo
- O hacer que la sección de reemplazo reemplace espacio existente
- O usar CSS Grid con `align-items: stretch`

### 4.4 Botones de Evento Desordenados

**Problema**: Botones de diferentes tamaños, colores y propósitos mezclados sin jerarquía visual.

**Fix**: Agrupar por categoría:
- Navegación: Volver
- Estado: CERRADA/ABIERTA
- Acciones: Reabrir, Eliminar
- Reportes: Reporte, Excel, PDF
- Utilidades: QR

---

## 5. Endpoints y Rutas Relevantes

### 5.1 Dashboard
```
GET /dashboard
Controller: TableroController@index
```

### 5.2 Refuerzos
```
POST /admin/guardias/refuerzo/assign
Controller: AdministradorController@assignRefuerzo

POST /admin/guardias/refuerzo/remove
Controller: AdministradorController@removeRefuerzo
```

### 5.3 Reemplazos
```
POST /admin/guardias/replacement/create
Controller: AdministradorController@createReplacement

POST /admin/guardias/replacement/{id}/undo
Controller: AdministradorController@undoReplacement
```

### 5.4 Asistencia
```
POST /admin/guardias/{guardia}/bulk-update
Controller: AdministradorController@bulkUpdateGuardia
```

---

## 6. Modelos de Datos

### 6.1 Bombero (Campos Relevantes)
```php
'guardia_id' => 'ID de la guardia actual',
'estado_asistencia' => 'constituye|reemplazo|permiso|ausente|licencia|falta',
'es_titular' => 'boolean - Es bombero titular de la guardia',
'es_refuerzo' => 'boolean - Es refuerzo de otra guardia',
'refuerzo_guardia_anterior_id' => 'ID de la guardia original (para devolver)',
'es_jefe_guardia' => 'boolean',
'fuera_de_servicio' => 'boolean',
```

### 6.2 ReemplazoBombero
```php
'guardia_id' => 'ID de la guardia donde ocurre',
'bombero_titular_id' => 'Bombero original que es reemplazado',
'bombero_reemplazante_id' => 'Bombero que cubre',
'inicio' => 'datetime',
'fin' => 'datetime|null',
'estado' => 'activo|completado|cancelado',
```

---

## 7. Soft Refresh y Sincronización

### 7.1 Mecanismo de Actualización

El dashboard usa polling para detectar cambios:

```javascript
// Cada 5 segundos
fetch('/guardia/snapshot')
    .then(r => r.json())
    .then(data => {
        if (data.latest_bombero_at > lastKnownBomberoAt) {
            softRefreshGuardiaDashboard();
        }
    });
```

### 7.2 Problema de Sincronización

El soft-refresh reemplaza el HTML del grid, pero:
- Pierde el estado de confirmaciones (debería restaurarse de sessionStorage)
- Puede causar "saltos" visuales
- No hay indicador de "actualizando..."

---

## 8. Recomendaciones de Mejora

### 8.1 Prioridad Alta
1. **Fix auto-reset de refuerzos**: Agregar bandera `es_refuerzo_manual` que ignore el reset
2. **Fix layout tarjetas**: Usar flexbox con alturas dinámicas
3. **Mejorar select de reemplazo**: Componente de búsqueda con debounce

### 8.2 Prioridad Media
4. **Indicador de sincronización**: Mostrar cuando hay nuevos datos
5. **Optimizar queries**: Eager loading de relaciones faltantes
6. **Cache de candidatos**: Los candidatos a reemplazo no cambian frecuentemente

### 8.3 Prioridad Baja
7. **Dark mode consistente**: Algunos elementos usan colores claros
8. **Accesibilidad**: Mejorar contraste y navegación por teclado
9. **Tests**: Agregar tests de integración para el flujo completo

---

## 9. Flujos de Usuario

### 9.1 Agregar Refuerzo
```
Usuario → Click ícono + → Modal → Seleccionar bombero → Submit
→ Controller: assignRefuerzo() → Update bombero.guardia_id
→ Response: redirect back con mensaje
→ Frontend: softRefreshGuardiaDashboard()
→ Dashboard recarga: ¡PERO el reset lo quita!
```

### 9.2 Crear Reemplazo
```
Usuario → Click REEMPLAZAR → Modal → Buscar reemplazante → Seleccionar → Submit
→ Controller: createReplacement() → Crear registro en reemplazos_bomberos
→ Response: redirect back
→ Frontend: softRefreshGuardiaDashboard()
→ Dashboard muestra: Tarjeta con sección de reemplazo
```

### 9.3 Registrar Asistencia
```
Usuario → Cambia estados (click ciclos) → Confirma códigos → Click Guardar
→ Submit form → Controller: bulkUpdateGuardia()
→ Valida tokens de confirmación
→ Update estado_asistencia de cada bombero
→ Crea registro en GuardiaAttendanceRecord
→ Response: redirect back con éxito
```

---

## 10. Dependencias Externas

### 10.1 Servicios
- `ReplacementService::expire()`: Limpia reemplazos vencidos
- `SystemSetting::getValue()`: Configuración dinámica

### 10.2 Configuración
```php
'guardia_schedule_tz' => 'America/Santiago',  // Zona horaria
'guardia_daily_end_time' => '07:00',            // Hora de corte
```

---

## 11. Métricas y Monitoreo

Recomendaciones para monitoreo:
- Tiempo de carga del dashboard (debería ser < 500ms)
- Tasa de errores en confirmaciones
- Frecuencia de soft-refreshes innecesarios
- Bomberos en estado inconsistente (refuerzo sin guardia anterior)

---

## 12. Conclusión

El Perfil Guardia es una interfaz compleja con múltiples estados interconectados. Los problemas actuales son:

1. **Funcional**: El auto-reset de refuerzos es agresivo y elimina trabajo del usuario
2. **UX**: El select de reemplazo y el layout de tarjetas necesitan atención visual
3. **Técnico**: El soft-refresh puede optimizarse para reducir parpadeos

La arquitectura general es sólida, pero necesita ajustes en los detalles de implementación para una experiencia de usuario fluida.

---

**Documento generado por**: Cascade AI
**Sistema**: Guardia Nocturna v1.0
**Fecha**: Febrero 2025
