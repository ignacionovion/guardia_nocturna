# Validación Arquitectura SaaS GuardiAPP

**Fecha:** Marzo 2026  
**Versión:** 1.0  
**Estado:** Implementación Completada - Pendiente Validación en Producción

---

## Resumen de Implementación

Se implementó una arquitectura SaaS profesional con separación clara entre:
- **Módulos del Sistema** (11 features operativas)
- **Addons SaaS** (8 features comerciales)

---

## 1. Checklist de Validación

### ✅ 1.1 Separación Features/Addons

| Componente | Estado | Ubicación |
|------------|--------|-----------|
| Modelo Plan.php con `features` y `addons` | ✅ | `app/Models/Plan.php:22-23` |
| Cast array para ambos campos | ✅ | `app/Models/Plan.php:29-31` |
| Método `availableModules()` separado | ✅ | `app/Models/Plan.php:39-54` |
| Método `availableAddons()` separado | ✅ | `app/Models/Plan.php:65-77` |
| UI muestra secciones separadas | ✅ | `resources/views/central/tenants/show.blade.php:311-365` |

**Verificación:**
```bash
php artisan tinker
>>> App\Models\Plan::availableModules();
>>> App\Models\Plan::availableAddons();
```

### ✅ 1.2 Plan Básico - 5 Módulos Exactos

| Módulo | Estado en Básico | Validación |
|--------|------------------|------------|
| voluntarios | ✅ true | Plan.php:94 |
| dotaciones | ✅ true | Plan.php:97 |
| calendario | ✅ true | Plan.php:98 |
| guardia | ✅ true | Plan.php:99 |
| camas | ✅ true | Plan.php:100 |
| emergencias | ✅ false | Plan.php:95 |
| now | ✅ false | Plan.php:98 |
| reportes | ✅ false | Plan.php:101 |
| planilla | ✅ false | Plan.php:102 |
| preventiva | ✅ false | Plan.php:103 |
| inventario | ✅ false | Plan.php:104 |

**Verificación:**
```bash
php artisan tinker
>>> App\Models\Plan::getDefaultFeaturesForPlan('basico');
```

### ✅ 1.3 UI Panel SaaS Separada

| Sección | Icono | Color | Líneas |
|---------|-------|-------|--------|
| Módulos del Sistema | Grid (blue) | emerald | 311-337 |
| Addons SaaS | Sliders (purple) | purple | 339-365 |

**Elementos verificados:**
- Títulos con iconos distintivos ✅
- Colores diferentes (emerald vs purple) ✅
- Labels desde Plan.php ✅
- Badges "CUSTOM" para overrides ✅

### ✅ 1.4 FeatureFlagService - Fuente Única

| Aspecto | Implementación | Línea |
|---------|----------------|-------|
| Sin listas duplicadas | Usa `Plan::availableModules()` | 169, 192 |
| Sin $planDefaults estático | Delega a Plan.php | N/A |
| Métodos específicos | `moduleEnabled()`, `addonEnabled()` | 37, 55 |
| Labels dinámicos | `moduleLabels()`, `addonLabels()` | 250, 258 |

**Verificación:**
```bash
grep -n "availableModules\|availableAddons" app/Services/FeatureFlagService.php
```

### ✅ 1.5 Menús y Rutas con Feature Checks

| Ubicación | Tipo | Feature Check |
|-----------|------|---------------|
| Menú Gestión | Dropdown | voluntarios, emergencias, dotaciones, calendario |
| Menú Guardias | Dropdown | now, guardia, camas, reportes |
| Menú Preventivas | Item | preventiva |
| Menú Planillas | Item | planilla |
| Menú Inventario | Item | inventario |
| Rutas Calendario | Middleware | feature:calendario |
| Rutas Reportes | Middleware | feature:reportes |
| Rutas Emergencias | Middleware | feature:emergencias |
| Rutas Inventario | Middleware | feature:inventario |
| Rutas Planillas | Middleware | feature:planilla |
| Rutas Preventivas | Middleware | feature:preventiva |

**Verificación:**
```bash
grep -n "feature(" resources/views/layouts/app.blade.php
grep -n "middleware.*feature" routes/app.php
```

### ✅ 1.6 Migración Segura

| Característica | Implementación |
|----------------|----------------|
| Verifica columna existente | `hasColumn()` check |
| No borra datos | Update, no delete |
| Sincroniza features | `syncPlansWithDefaults()` |
| Crea planes faltantes | `Plan::create()` |
| Columna nullable | `nullable()->after('features')` |

**Rollback seguro:**
```bash
php artisan migrate:rollback --step=1
```

### ✅ 1.7 Preparado para Overrides

| Componente | Soporte |
|------------|---------|
| Tenant->features JSON | ✅ Almacena overrides |
| FeatureFlagService->set() | ✅ Guarda override |
| FeatureFlagService->reset() | ✅ Elimina override |
| FeatureFlagService check | Revisa $tenant->features primero |
| Badge "CUSTOM" en UI | ✅ Muestra overrides activos |

### 1.8 Cobertura de Módulos (⚠️ Parcial)

| Módulo | Menú | Rutas | Middleware |
|--------|------|-------|------------|
| voluntarios | ✅ | N/A (siempre disponible) | N/A |
| emergencias | ✅ | ✅ | ✅ |
| dotaciones | ✅ | ✅ | ✅ |
| calendario | ✅ | ✅ | ✅ |
| now | ✅ | N/A (ruta /now) | N/A |
| guardia | ✅ | N/A (core) | N/A |
| camas | ✅ | N/A (core) | N/A |
| reportes | ✅ | ✅ | ✅ |
| planilla | ✅ | ✅ | ✅ |
| preventiva | ✅ | ✅ | ✅ |
| inventario | ✅ | ✅ | ✅ |

⚠️ **Pendiente:** Menú de capitanía (líneas 192-218) tiene items sin feature check:
- Guardias, Calendario, Voluntarios, Usuarios

---

## 2. Riesgos Detectados

### 🔴 Riesgo Alto: Menú Capitanía sin Feature Checks

**Ubicación:** `resources/views/layouts/app.blade.php:192-218`

**Problema:** Los siguientes items no verifican features:
```blade
<a href="{{ route('admin.guardias') }}">Guardias</a>  // Sin @if(feature('guardia'))
<a href="{{ route('admin.calendario') }}">Calendario</a>  // Sin @if(feature('calendario'))
<a href="{{ route('admin.volunteers.index') }}">Voluntarios</a>  // Sin @if(feature('voluntarios'))
```

**Impacto:** Usuarios con rol capitanía pueden ver menús de módulos deshabilitados en su plan.

**Mitigación:** Las rutas tienen middleware, pero el menú se mostrará igual.

### 🟡 Riesgo Medio: Límite de Guardias/Beds no validado en frontend

**Problema:** Los botones "Nuevo" no se ocultan cuando se alcanza el límite del plan.

**Ubicación:** `resources/views/admin/volunteers/index.blade.php`

**Solución propuesta:**
```blade
@if(!plan_exceeded('guardias'))
    <button>Nuevo Voluntario</button>
@endif
```

### 🟢 Riesgo Bajo: Cache de Plan

**Problema:** PlanService no implementa caché para consultas frecuentes.

**Impacto:** Cada `feature()` query hace SELECT a base de datos.

**Mitigación:** Laravel query cache maneja esto en práctica.

---

## 3. Cosas que Aún Faltarían Ajustar

### Prioridad Alta

1. **Menú Capitanía con feature checks**
   - Archivo: `resources/views/layouts/app.blade.php:192-218`
   - Agregar `@if(feature('xxx'))` a cada item

2. **Botones de creación condicionales por límite**
   - Vistas: `admin/volunteers/index`, `admin/users/index`
   - Usar `plan_exceeded()` helper

### Prioridad Media

3. **Feature check para rutas de voluntarios**
   - Actualmente voluntarios es siempre accesible
   - Considerar si necesita `feature:voluntarios` middleware

4. **Validación de límites en controladores**
   - `BomberoController@store` - validar límite guardias
   - `AsignacionCamaController@store` - validar límite beds

5. **Mensajes amigables cuando se alcanza límite**
   - Actualmente redirecciona con mensaje genérico
   - Mejorar UX con mensaje específico del plan

### Prioridad Baja

6. **Cache en FeatureFlagService**
   - Agregar TTL cache para reducir queries
   - Invalidar cache al cambiar plan/features

7. **Tests automatizados**
   - Test de feature access por plan
   - Test de middleware
   - Test de límites

---

## 4. Comandos de Ejecución

### 4.1 Local (Desarrollo)

```bash
# 1. Verificar estado actual de planes
php artisan tinker --execute="print_r(App\Models\Plan::all()->toArray());"

# 2. Ejecutar migración (agrega columna addons y sincroniza)
php artisan migrate

# 3. Verificar planes sincronizados
php artisan plans:sync --dry-run  # Si tuviera modo dry-run

# 4. Verificar estructura
php artisan tinker
>>> App\Models\Plan::first()->features;
>>> App\Models\Plan::first()->addons;

# 5. Limpiar caché
php artisan optimize:clear

# 6. Verificar helper feature()
php artisan tinker
>>> feature('voluntarios');  // Debe retornar true/false
```

### 4.2 Producción (Staging primero)

```bash
# PRE-DESPLIEGUE (en staging)
# ===========================

# 1. Backup base de datos central
mysqldump -u root -p appdev_guardiasas > backup_pre_saas_$(date +%Y%m%d).sql

# 2. Verificar planes actuales
php artisan tinker --execute="print_r(App\Models\Plan::all()->pluck('slug')->toArray());"

# 3. Ejecutar migración
php artisan migrate --force

# 4. Verificar migración aplicada
php artisan migrate:status

# 5. Verificar datos de planes
php artisan tinker
>>> Plan::where('slug', 'basico')->first()->features;

# POST-DESPLIEGUE
# ===============

# 6. Verificar tenants existentes
php artisan tinker
>>> Tenant::count();
>>> Tenant::first()->plan_id;

# 7. Probar feature() en contexto tenant
# Acceder a un tenant y verificar:
# - Menú muestra solo módulos habilitados
# - Rutas protegidas por middleware
```

### 4.3 Rollback (si es necesario)

```bash
# Rollback de migración
php artisan migrate:rollback --step=1

# Restaurar backup
mysql -u root -p appdev_guardiasas < backup_pre_saas_YYYYMMDD.sql

# Limpiar caché
php artisan optimize:clear
```

---

## 5. Pruebas Manuales Requeridas

### 5.1 Plan Básico

| # | Prueba | Resultado Esperado | Cómo Verificar |
|---|--------|-------------------|----------------|
| 1 | Login tenant básico | Acceso normal | Ingresar con credenciales |
| 2 | Ver menú Gestión | Solo: Voluntarios, Dotaciones, Calendario | Revisar dropdown |
| 3 | Ver menú Guardias | Solo: Guardias, Camas (sin Now, sin Reportes) | Revisar dropdown |
| 4 | No ver Inventario | Item no aparece | No debe estar en menú |
| 5 | No ver Planillas | Item no aparece | No debe estar en menú |
| 6 | No ver Preventivas | Item no aparece | No debe estar en menú |
| 7 | Acceder /admin/emergencies | Error 403 o redirect | Middleware bloquea |
| 8 | Acceder /admin/reports | Error 403 o redirect | Middleware bloquea |
| 9 | Acceder /inventario | Error 403 o redirect | Middleware bloquea |
| 10 | Crear voluntario #21 | Error límite | Mensaje plan_exceeded |

### 5.2 Plan Profesional

| # | Prueba | Resultado Esperado |
|---|--------|-------------------|
| 1 | Ver menú Gestión | Voluntarios, Emergencias, Dotaciones, Calendario |
| 2 | Ver menú Guardias | Guardias, Camas + Reportes (sin Now) |
| 3 | Ver Planillas | Item visible |
| 4 | No ver Inventario | Item no aparece |
| 5 | No ver Preventivas | Item no aparece |
| 6 | Acceder Reportes | Funciona correctamente |

### 5.3 Plan Enterprise

| # | Prueba | Resultado Esperado |
|---|--------|-------------------|
| 1 | Ver todos los módulos | Todos visibles |
| 2 | Ver Now | Item visible en menú Guardias |
| 3 | Ver Preventivas | Item visible |
| 4 | Ver Inventario | Item visible |
| 5 | Ver Addons | Backup, API, etc. visibles en panel SaaS |

### 5.4 Panel SaaS (Central)

| # | Prueba | Resultado Esperado |
|---|--------|-------------------|
| 1 | Abrir tenant básico | Muestra 11 módulos + 8 addons en secciones separadas |
| 2 | Módulos del Sistema | Icono azul, títulos correctos |
| 3 | Addons SaaS | Icono púrpura, títulos correctos |
| 4 | Toggle feature | Guarda override en tenant->features |
| 5 | Badge CUSTOM | Aparece al modificar un valor |
| 6 | Cambiar plan | Dropdown funciona, cambia plan_id |

### 5.5 Overrides por Tenant

| # | Prueba | Resultado Esperado |
|---|--------|-------------------|
| 1 | Habilitar inventario en básico | Toggle en panel SaaS |
| 2 | Acceder como tenant | Inventario ahora visible |
| 3 | Verificar persistencia | Recargar página, sigue habilitado |
| 4 | Resetear a plan default | Eliminar override, vuelve a false |

---

## 6. Estructura Final de Archivos

```
app/
├── Models/
│   └── Plan.php                    # ✅ Refactorizado con modules/addons
├── Services/
│   └── FeatureFlagService.php      # ✅ Usa Plan.php como fuente única
├── Console/Commands/
│   └── SyncPlansCommand.php        # ✅ Nuevo comando
├── Http/Middleware/
│   └── EnforceFeatureFlag.php      # ✅ Ya existía

database/
├── migrations/
│   └── 2024_03_12_000001_add_addons_to_plans_table.php  # ✅ Nueva migración

resources/
├── views/
│   ├── layouts/app.blade.php       # ✅ Menú con feature checks
│   └── central/tenants/show.blade.php  # ✅ UI separada módulos/addons

routes/
└── app.php                         # ✅ Middleware feature en rutas
```

---

## 7. Diagrama de Flujo de Feature Check

```
┌─────────────────────────────────────────────────────────────┐
│                    feature('modulo')                         │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│           FeatureFlagService::enabled('modulo')              │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  1. ¿Tenant tiene override en $tenant->features?           │
│     └── SÍ → Retorna valor del override                     │
│     └── NO → Continúa                                      │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  2. ¿Tenant tiene plan_id?                                 │
│     └── SÍ → Plan::find($plan_id)                          │
│     └── NO → Plan::where('slug', $tenant->plan)->first()   │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  3. $plan->hasFeature('modulo')                             │
│     └── Busca en $plan->features[]                          │
│     └── Luego en $plan->addons[]                            │
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│  4. Fallback: Plan::getDefaultFeaturesForPlan('basico')    │
└─────────────────────────────────────────────────────────────┘
```

---

## 8. Contacto y Soporte

Para reportar problemas con la arquitectura SaaS:
1. Revisar logs en `storage/logs/`
2. Verificar estado de planes: `php artisan plans:sync`
3. Consultar documentación técnica adicional en `/docs/`

---

**Fin del Documento**
