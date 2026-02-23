# Flujo de Reemplazos y Refuerzos - Guardia Nocturna

## Resumen Ejecutivo

Este documento explica el flujo completo de **reemplazos** y **refuerzos** de bomberos en el sistema:
1. Diferencias entre reemplazos y refuerzos
2. Cómo se registran en la base de datos
3. Horario de salida (07:00 AM) y cómo se ejecuta
4. Conexión con el reseteo de tarjetas del dashboard
5. Problemas conocidos y cómo depurarlos

---

## Diferencias Clave: Reemplazo vs Refuerzo

| Aspecto | REEMPLAZO | REFUERZO |
|---------|-----------|----------|
| **Propósito** | Un bombero sustituye a otro que no puede asistir | Un bombero de otra guardia apoya temporalmente |
| **Quién sale** | El bombero original (titular) queda "ausente" | El bombero de otra guardia viene como "refuerzo" |
| **Quién entra** | El reemplazante (puede ser de cualquier guardia) | El bombero refuerzo (de otra guardia) |
| **Registro en BD** | Tabla `reemplazos_bomberos` | Campo `es_refuerzo = true` en tabla `bomberos` |
| **Campos BD** | `inicio`, `fin`, `estado`, `bombero_titular_id`, `bombero_reemplazante_id` | `es_refuerzo`, `refuerzo_guardia_anterior_id` |
| **Vuelve a su guardia** | Sí, al terminar el reemplazo | Sí, al terminar el refuerzo |

---

## 1. REEMPLAZOS - Flujo Completo

### Tabla: `reemplazos_bomberos`

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | bigint | PK |
| `guardia_id` | bigint | FK → guardias (qué guardia recibe el reemplazo) |
| `bombero_titular_id` | bigint | FK → bomberos (el que es reemplazado) |
| `bombero_reemplazante_id` | bigint | FK → bomberos (el que reemplaza) |
| `inicio` | datetime | Cuándo inicia el reemplazo |
| `fin` | datetime | Cuándo termina (calculado automáticamente) |
| `estado` | enum | `activo` o `cerrado` |
| `notas` | text | JSON con `replacement_previous_guardia_id` |

### Cómo se crea un reemplazo

**Archivo:** `app/Http/Controllers/AdministradorController.php:490-590`

```php
public function assignReplacement(Request $request)
{
    // ... validaciones ...
    
    $endsAt = ReplacementService::calculateReplacementUntil(Carbon::now());
    
    DB::transaction(function () use ($guardia, $original, $replacement, $endsAt, $shift) {
        // 1. Crear registro en reemplazos_bomberos
        ReemplazoBombero::create([
            'guardia_id' => $guardia->id,
            'bombero_titular_id' => $original->id,
            'bombero_reemplazante_id' => $replacement->id,
            'inicio' => Carbon::now(),           // ← AHORA
            'fin' => $endsAt,                    // ← Calculado (07:00 AM próximo día hábil)
            'estado' => 'activo',
            'notas' => json_encode([
                'replacement_previous_guardia_id' => $replacement->guardia_id, // ← Para devolverlo después
            ]),
        ]);

        // 2. Mover reemplazante a la guardia
        $replacement->update([
            'guardia_id' => $guardia->id,
            'estado_asistencia' => 'constituye',
            'es_titular' => false,
            'es_jefe_guardia' => false,
            'es_cambio' => false,
            'es_sancion' => false,
        ]);

        // 3. Marcar original como ausente
        $original->update([
            'estado_asistencia' => 'ausente',
            'es_jefe_guardia' => false,
            'es_cambio' => false,
            'es_sancion' => false,
        ]);

        // 4. Crear/actualizar registro en shift_users (turno actual)
        if ($shift) {
            ShiftUser::updateOrCreate(
                ['shift_id' => $shift->id, 'firefighter_id' => $replacement->id],
                [
                    'guardia_id' => $guardia->id,
                    'attendance_status' => 'constituye',
                    'assignment_type' => 'Reemplazo',
                    'replaced_firefighter_id' => $original->id,
                    'present' => true,
                    'start_time' => $shift->created_at,
                    'end_time' => null,
                ]
            );
        }
    });
}
```

### Cálculo del horario de fin

**Archivo:** `app/Services/ReplacementService.php:13-40`

```php
public static function calculateReplacementUntil(Carbon $at): Carbon
{
    $scheduleTz = env('GUARDIA_SCHEDULE_TZ', 'America/Santiago');
    $atLocal = $at->copy()->setTimezone($scheduleTz);

    // Horario de constitución: 23:00 (L-S) o 22:00 (Dom)
    $scheduleHourToday = $atLocal->isSunday() ? 22 : 23;
    $todayStart = $atLocal->copy()->startOfDay()->addHours($scheduleHourToday);

    if ($atLocal->greaterThanOrEqualTo($todayStart)) {
        $shiftStart = $todayStart;
    } else {
        if ($atLocal->hour < 7) {  // Si son antes de las 7 AM
            $yesterday = $atLocal->copy()->subDay();
            $scheduleHourYesterday = $yesterday->isSunday() ? 22 : 23;
            $shiftStart = $yesterday->copy()->startOfDay()->addHours($scheduleHourYesterday);
        } else {
            $shiftStart = $todayStart;
        }
    }

    // FIN: Día siguiente a las 07:00 AM (los bomberos trabajan 365 días del año)
    $expiresAtLocal = $shiftStart->copy()->addDay()->startOfDay()->addHours(7);

    return $expiresAtLocal->setTimezone(config('app.timezone'));
}
```

**Ejemplo:**
- Reemplazo creado: Lunes 23:30
- Fin calculado: Martes 07:00 AM

**NOTA:** Los bomberos trabajan 365 días del año, incluyendo fines de semana y feriados. El fin del reemplazo siempre es el día siguiente a las 07:00 AM, sin saltar ningún día.

---

## 2. REFUERZOS - Flujo Completo

### Campos en tabla `bomberos`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `es_refuerzo` | boolean | ¿Es refuerzo temporal? |
| `refuerzo_guardia_anterior_id` | bigint | FK → guardias (de dónde viene) |

### Cómo se agrega un refuerzo

**Archivo:** `app/Http/Controllers/AdministradorController.php:369-421`

```php
public function assignRefuerzo(Request $request)
{
    // ... validaciones ...
    
    $prevGuardiaId = $firefighter->guardia_id;  // ← Guardar guardia origen

    $firefighter->update([
        'guardia_id' => $guardia->id,              // ← Mover a nueva guardia
        'estado_asistencia' => 'constituye',
        'es_titular' => false,
        'es_jefe_guardia' => false,
        'es_refuerzo' => true,                   // ← Marcar como refuerzo
        'refuerzo_guardia_anterior_id' => $prevGuardiaId,  // ← Guardar origen
        'es_cambio' => false,
        'es_sancion' => false,
    ]);
}
```

**Diferencia clave:** Los refuerzos NO crean registro en `reemplazos_bomberos`. Solo modifican el campo `es_refuerzo` del bombero.

---

## 3. LIMPIEZA AUTOMÁTICA A LAS 07:00 AM

### Dónde está configurado

**Archivo:** `routes/console.php:73-217`

Comando: `guardia:daily-cleanup`

Horario de ejecución: **`guardia_daily_end_time`** (por defecto `07:00`)
Configurado en: `app/Http/Controllers/Admin/SystemAdminController.php:33`

```php
'guardia_daily_end_time' => SystemSetting::getValue('guardia_daily_end_time', '07:00'),
```

### Qué hace el cleanup

```php
Artisan::command('guardia:daily-cleanup', function () {
    $cleanupTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');
    [$cleanupH, $cleanupM] = array_map('intval', explode(':', (string) $cleanupTime));
    $runAt = $nowLocal->copy()->startOfDay()->addHours($cleanupH)->addMinutes($cleanupM);
    $windowEnd = $runAt->copy()->addMinutes(5);  // Ventana de 5 minutos
    
    if (!($nowLocal->greaterThanOrEqualTo($runAt) && $nowLocal->lessThan($windowEnd))) {
        return;  // No es hora de ejecutar
    }

    // 1. CERRAR REEMPLAZOS VENCIDOS
    $activeReplacements = ReemplazoBombero::with(['originalFirefighter', 'replacementFirefighter'])
        ->where('estado', 'activo')
        ->get();

    foreach ($activeReplacements as $rep) {
        $repLocalDate = $localDateString($rep->inicio);
        if (!$repLocalDate || $repLocalDate >= $todayLocal) {
            continue;  // Reemplazo de hoy, no tocar
        }

        // Cerrar reemplazo
        $rep->update([
            'estado' => 'cerrado',
            'fin' => $nowApp,
        ]);

        // Restaurar bombero original
        $original->update([
            'estado_asistencia' => 'constituye',
            'es_jefe_guardia' => false,
            'es_cambio' => false,
            'es_sancion' => false,
        ]);

        // Devolver reemplazante a su guardia original
        $replacer->update([
            'guardia_id' => $prevGuardiaId,  // ← De las notas JSON
            'estado_asistencia' => 'constituye',
            'es_titular' => false,
            'es_jefe_guardia' => false,
            'es_refuerzo' => false,
            'es_cambio' => false,
            'es_sancion' => false,
        ]);
    }

    // 2. SACAR REFUERZOS (no titulares)
    $nonTitular = Bombero::query()
        ->whereNotNull('guardia_id')
        ->where('es_titular', false)
        ->get();

    foreach ($nonTitular as $bombero) {
        $prevGuardiaId = $bombero->refuerzo_guardia_anterior_id;
        
        $bombero->update([
            'guardia_id' => $prevGuardiaId,  // ← Volver a guardia anterior
            'estado_asistencia' => 'constituye',
            'es_refuerzo' => false,
            'refuerzo_guardia_anterior_id' => null,
            'es_jefe_guardia' => false,
            'es_cambio' => false,
            'es_sancion' => false,
        ]);
    }

    // 3. RESETear estados de bomberos en permiso/licencia/ausente/falta
    $temporales = Bombero::query()
        ->whereIn('estado_asistencia', ['ausente', 'permiso', 'licencia', 'falta'])
        ->get();

    foreach ($temporales as $bombero) {
        $bombero->update([
            'estado_asistencia' => 'constituye',
        ]);
    }
});
```

**Nota importante:** El cleanup solo procesa reemplazos creados **antes de hoy** (`$repLocalDate < $todayLocal`). Los reemplazos creados hoy no se tocan.

---

## 4. CONEXIÓN CON EL DASHBOARD (Reseteo de Tarjetas)

### Cómo el dashboard muestra reemplazos

**Archivo:** `app/Http/Controllers/AdministradorController.php:148-350` (método `index()`)

```php
public function index()
{
    // Limpieza automática de reemplazos vencidos (se ejecuta en cada carga)
    ReplacementService::expire(Carbon::now());
    
    // Cargar reemplazos activos
    $activeReplacements = ReemplazoBombero::with(['originalFirefighter', 'replacementFirefighter'])
        ->where('guardia_id', $guardia->id)
        ->where('estado', 'activo')
        ->get();

    // En la vista, para cada bombero se verifica:
    // - ¿Es reemplazante? → Muestra "REEMPLAZO" y quién reemplaza
    // - ¿Es reemplazado? → Muestra "REEMPLAZADO POR: [nombre]"
}
```

### Cómo el dashboard muestra refuerzos

```php
// El bombero tiene es_refuerzo = true
if ($bombero->es_refuerzo) {
    // Muestra badge "REFUERZO"
    // Muestra estado como "constituye" (requiere confirmación)
}
```

### Reseteo de tarjetas

Cuando un bombero **sale a las 07:00 AM** (por cleanup o expiración):

1. **Reemplazante:**
   - `guardia_id` → vuelve a su guardia original
   - `estado_asistencia` → `constituye`
   - Desaparece de la guardia actual

2. **Refuerzo:**
   - `guardia_id` → vuelve a `refuerzo_guardia_anterior_id`
   - `es_refuerzo` → `false`
   - `refuerzo_guardia_anterior_id` → `null`
   - Desaparece de la guardia actual

3. **Original (reemplazado):**
   - `estado_asistencia` → `constituye` (vuelve a estar disponible)

### Persistencia en sessionStorage

Las confirmaciones de asistencia se guardan en `sessionStorage` del navegador para sobrevivir refreshes, pero **no sobreviven entre días**. Al cambiar el día (07:00 AM cleanup), los bomberos cambian de estado y las confirmaciones anteriores ya no aplican.

---

## 5. PROBLEMAS CONOCIDOS Y DEPURACIÓN

### Problema 1: Reemplazos no se cierran a las 07:00

**Verificar:**
```bash
# Ver reemplazos activos
SELECT * FROM reemplazos_bomberos WHERE estado = 'activo';

# Verificar campo 'fin'
SELECT inicio, fin, estado FROM reemplazos_bomberos WHERE estado = 'activo';
```

**Causas comunes:**
1. El cron no está corriendo `guardia:daily-cleanup`
2. La zona horaria está mal configurada (`guardia_schedule_tz`)
3. El reemplazo se creó hoy (no se cierra hasta mañana)

**Solución manual:**
```bash
php artisan guardia:daily-cleanup --at="2026-02-24 07:01" --tz="America/Santiago"
```

### Problema 2: Refuerzos no vuelven a su guardia

**Verificar:**
```sql
SELECT id, nombres, guardia_id, es_refuerzo, refuerzo_guardia_anterior_id 
FROM bomberos 
WHERE es_refuerzo = 1 OR refuerzo_guardia_anterior_id IS NOT NULL;
```

**Causas comunes:**
1. El cleanup no se ejecutó
2. `refuerzo_guardia_anterior_id` es NULL (no se guardó al agregar)

### Problema 3: Dashboard muestra bomberos que ya salieron

**Verificar:**
1. ¿Se ejecutó `ReplacementService::expire()`?
2. ¿Está el bombero marcado con `estado = 'activo'` en `reemplazos_bomberos`?

**Forzar limpieza manual:**
```bash
php artisan guardia:expire-replacements
```

---

## 6. CASO ESPECIAL: EMERGENCIAS POST-MEDIANOCHE

### El Problema

**Escenario:**
- Domingo 23:30: Sale una emergencia
- Lunes 03:20: Regresan y se registran reemplazos/refuerzos
- Lunes 07:00: El sistema elimina estos reemplazos/refuerzos (¡incorrecto!)

**¿Por qué ocurre?**
El `calculateReplacementUntil()` calcula el fin como "día siguiente a las 07:00". Si se registra a las 03:20 del Lunes, calcula:
- Inicio del turno: Domingo 23:00 (correcto - es el turno activo)
- Fin: Domingo 23:00 + 1 día = Lunes 07:00 (¡incorrecto! debería ser Martes 07:00)

El problema es que el reemplazo se registra "tarde" (después de medianoche) pero pertenece al turno del día anterior. Solo duraría ~4 horas en lugar de ~24 horas.

### Solución Propuesta

**Opción A: Extender automáticamente si es post-medianoche (Recomendada)**

Modificar `ReplacementService::calculateReplacementUntil()` para detectar si la hora actual es entre 00:00 y 07:00, y en ese caso extender el fin un día adicional:

```php
public static function calculateReplacementUntil(Carbon $at): Carbon
{
    $scheduleTz = env('GUARDIA_SCHEDULE_TZ', 'America/Santiago');
    $atLocal = $at->copy()->setTimezone($scheduleTz);

    // Horario de constitución: 23:00 (L-S) o 22:00 (Dom)
    $scheduleHourToday = $atLocal->isSunday() ? 22 : 23;
    $todayStart = $atLocal->copy()->startOfDay()->addHours($scheduleHourToday);

    if ($atLocal->greaterThanOrEqualTo($todayStart)) {
        $shiftStart = $todayStart;
        $daysToAdd = 1;  // Normal: turno de hoy termina mañana
    } else {
        if ($atLocal->hour < 7) {  // Entre 00:00 y 07:00
            $yesterday = $atLocal->copy()->subDay();
            $scheduleHourYesterday = $yesterday->isSunday() ? 22 : 23;
            $shiftStart = $yesterday->copy()->startOfDay()->addHours($scheduleHourYesterday);
            $daysToAdd = 2;  // Especial: turno de ayer termina "pasado mañana" (para dar 2 días completos)
        } else {
            $shiftStart = $todayStart;
            $daysToAdd = 1;
        }
    }

    // FIN: Calcular según corresponda
    $expiresAtLocal = $shiftStart->copy()->addDays($daysToAdd)->startOfDay()->addHours(7);

    return $expiresAtLocal->setTimezone(config('app.timezone'));
}
```

**Ejemplos con la solución:**
- Reemplazo creado: Domingo 23:30 → Fin: Lunes 07:00 (1 día después)
- Reemplazo creado: Lunes 03:20 (post-emergencia) → Fin: Martes 07:00 (2 días después del inicio del turno)

**Opción B: Usar el turno activo (shift_id)**

En lugar de calcular basado en la hora actual, usar el `shift_id` activo para determinar la fecha del turno:

```php
public static function calculateReplacementUntil(Carbon $at, ?int $shiftId = null): Carbon
{
    if ($shiftId) {
        $shift = Shift::find($shiftId);
        if ($shift) {
            // El fin es el día después de la fecha del shift, a las 07:00
            return Carbon::parse($shift->date)
                ->addDay()
                ->startOfDay()
                ->addHours(7);
        }
    }
    
    // Fallback al cálculo actual
    // ... resto del método
}
```

**Ventajas de Opción A:**
- No requiere cambios en la firma del método
- Funciona automáticamente sin pasar parámetros adicionales
- Cubre el caso de emergencias post-medianoche

**Ventajas de Opción B:**
- Más preciso (usa la fecha real del turno)
- No depende de la hora de registro

### Implementación Recomendada

La **Opción A** es la más simple y cubre el 99% de los casos. Solo requiere modificar `ReplacementService.php`:

```php
// Línea 33 en ReplacementService.php
$expiresAtLocal = $shiftStart->copy()->addDays($daysToAdd)->startOfDay()->addHours(7);
```

Y agregar la lógica para determinar `$daysToAdd` (1 o 2) según la hora actual.

---

## 7. RESUMEN VISUAL DEL FLUJO

```
┌─────────────────────────────────────────────────────────────────┐
│  CREACIÓN (Usuario hace clic en "Reemplazar"/"Agregar refuerzo")│
└────────────────────────┬────────────────────────────────────────┘
                         │
         ┌───────────────┴───────────────┐
         │                               │
    REEMPLAZO                        REFUERZO
         │                               │
         ▼                               ▼
┌─────────────────┐           ┌─────────────────┐
│Tabla:           │           │Tabla:           │
│reemplazos_bomberos           │bomberos         │
│                 │           │                 │
│• inicio = now()│           │• es_refuerzo = 1 │
│• fin = 07:00    │           │• refuerzo_guardia│
│   próximo día   │           │  _anterior_id = X│
│• estado = activo│           │                 │
└────────┬────────┘           └────────┬────────┘
         │                             │
         └──────────────┬──────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────────┐
│  DURANTE LA GUARDIA (Dashboard muestra tarjetas)                 │
│                                                                  │
│  Reemplazante: "REEMPLAZO - Reemplaza a [original]"              │
│  Refuerzo: "REFUERZO" (badge)                                    │
│  Original: "AUSENTE" → "REEMPLAZADO POR: [reemplazante]"          │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ 07:00 AM (guardia_daily_end_time)
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  CLEANUP AUTOMÁTICO (guardia:daily-cleanup)                      │
└────────────────────────┬────────────────────────────────────────┘
         ┌───────────────┴───────────────┐
         │                               │
    REEMPLAZOS                       REFUERZOS
         │                               │
         ▼                               ▼
┌─────────────────┐           ┌─────────────────┐
│• estado = 'cerrado'          │• es_refuerzo = 0│
│• fin = now()    │           │• guardia_id =   │
│                 │           │  refuerzo_guardia│
│Reemplazante:    │           │  _anterior_id   │
│• Vuelve a su    │           │• refuerzo_guardia│
│  guardia origen │           │  _anterior_id = 0│
│                 │           │                 │
│Original:        │           │Bombero vuelve a │
│• estado =      │           │su guardia origen│
│  'constituye'   │           │                 │
└─────────────────┘           └─────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  DASHBOARD (Siguiente carga)                                     │
│                                                                  │
│  • Reemplazante desaparece (volvió a su guardia)                 │
│  • Refuerzo desaparece (volvió a su guardia)                     │
│  • Original aparece como "CONSTITUYE" (disponible)                │
└─────────────────────────────────────────────────────────────────┘
```

---

## 7. ARCHIVOS CLAVE

| Archivo | Propósito |
|---------|-----------|
| `app/Http/Controllers/AdministradorController.php` | Crear/des hacer reemplazos y refuerzos |
| `app/Services/ReplacementService.php` | Calcular fechas de expiración (365 días, incluye fines de semana/feriados) |
| `routes/console.php` | Comando `guardia:daily-cleanup` (07:00 AM) |
| `app/Http/Controllers/Admin/SystemAdminController.php` | Configuración de horarios |
| `resources/views/dashboard.blade.php` | Mostrar tarjetas con reemplazos/refuerzos |
| `app/Models/ReemplazoBombero.php` | Modelo de reemplazos |
| `app/Models/Bombero.php` | Modelo con campos `es_refuerzo`, `refuerzo_guardia_anterior_id` |

---

## 8. COMANDOS ÚTILES PARA DEPURAR

```bash
# Ver reemplazos activos
php artisan tinker --execute="print_r(ReemplazoBombero::where('estado','activo')->get()->toArray());"

# Ejecutar cleanup manualmente
php artisan guardia:daily-cleanup --at="2026-02-24 07:01" --tz="America/Santiago"

# Verificar expiración de reemplazos
php artisan guardia:expire-replacements

# Ver bomberos marcados como refuerzo
php artisan tinker --execute="print_r(Bombero::where('es_refuerzo',1)->get()->toArray());"
```

---

*Documento generado el 23 Feb 2026*
*Sistema Guardia Nocturna v1.0*
