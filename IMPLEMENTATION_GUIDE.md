# Guía de Implementación de Límites de Plan

## Resumen de Implementación

### ✅ Completado

1. **Middleware de Feature Gating**
   - Archivo: `app/Http/Middleware/EnsureTenantFeatureEnabled.php`
   - Alias: `tenant.feature`
   - Aplicado en rutas de: inventario, reportes, emergencias, planilla, preventiva

2. **Servicio de Límites**
   - Archivo: `app/Services/TenantPlanLimitService.php`
   - Métodos disponibles para validar límites de usuarios, camas, guardias, storage

### 🔄 Pendiente de Aplicar

#### 1. Límites en Creación de Usuarios

**Controlador a modificar:** `app/Http/Controllers/UserController.php` o `app/Http/Controllers/Admin/SystemUserController.php`

**Ejemplo de implementación:**

```php
use App\Services\TenantPlanLimitService;

public function __construct(
    protected TenantPlanLimitService $limitService
) {}

public function store(Request $request)
{
    // Validar límite ANTES de crear
    if (!$this->limitService->canCreateUser()) {
        return back()->withErrors([
            'limit' => $this->limitService->getLimitExceededMessage('users')
        ]);
    }

    $validated = $request->validate([...]);
    $user = User::create($validated);
    
    return redirect()->route('users.index')
        ->with('success', 'Usuario creado exitosamente.');
}
```

#### 2. Límites en Creación de Camas

**Controlador a modificar:** `app/Http/Controllers/BedController.php` o `app/Http/Controllers/CamaController.php`

**Ejemplo de implementación:**

```php
use App\Services\TenantPlanLimitService;

public function __construct(
    protected TenantPlanLimitService $limitService
) {}

public function store(Request $request)
{
    // Validar límite ANTES de crear
    if (!$this->limitService->canCreateBed()) {
        return back()->withErrors([
            'limit' => $this->limitService->getLimitExceededMessage('beds')
        ]);
    }

    $validated = $request->validate([...]);
    $bed = Bed::create($validated);
    
    return redirect()->route('beds.index')
        ->with('success', 'Cama creada exitosamente.');
}
```

#### 3. Límites en Creación de Guardias

**Controlador a modificar:** `app/Http/Controllers/AdministradorController.php`

**Ejemplo de implementación:**

```php
use App\Services\TenantPlanLimitService;

public function __construct(
    protected TenantPlanLimitService $limitService
) {}

public function storeGuardia(Request $request)
{
    // Validar límite ANTES de crear
    if (!$this->limitService->canCreateGuardia()) {
        return back()->withErrors([
            'limit' => $this->limitService->getLimitExceededMessage('guardias')
        ]);
    }

    $validated = $request->validate([...]);
    $guardia = Guardia::create($validated);
    
    return redirect()->route('admin.guardias.index')
        ->with('success', 'Guardia creada exitosamente.');
}
```

#### 4. Vista de Error 403 Personalizada

**Archivo a crear:** `resources/views/errors/feature-disabled.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Funcionalidad No Disponible')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <i class="fas fa-lock text-6xl text-gray-400 mb-4"></i>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Funcionalidad No Disponible
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                El módulo <strong>{{ $featureName }}</strong> no está incluido en tu plan actual.
            </p>
        </div>
        
        <div class="rounded-md bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3 flex-1 md:flex md:justify-between">
                    <p class="text-sm text-blue-700">
                        Para acceder a esta funcionalidad, actualiza tu plan o contacta al administrador.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver al Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
```

#### 5. Mostrar Uso Actual en UI

**Ejemplo para Dashboard o Vista de Configuración:**

```blade
@php
    $usage = app(\App\Services\TenantPlanLimitService::class)->getCurrentUsage();
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    {{-- Usuarios --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Usuarios</p>
                <p class="text-2xl font-bold">
                    {{ $usage['users']['current'] }}
                    @if(!$usage['users']['unlimited'])
                        / {{ $usage['users']['limit'] }}
                    @endif
                </p>
            </div>
            <div class="text-blue-500">
                <i class="fas fa-users text-3xl"></i>
            </div>
        </div>
        @if(!$usage['users']['unlimited'])
            <div class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" 
                         style="width: {{ ($usage['users']['current'] / $usage['users']['limit']) * 100 }}%">
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Camas --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Camas</p>
                <p class="text-2xl font-bold">
                    {{ $usage['beds']['current'] }}
                    @if(!$usage['beds']['unlimited'])
                        / {{ $usage['beds']['limit'] }}
                    @endif
                </p>
            </div>
            <div class="text-green-500">
                <i class="fas fa-bed text-3xl"></i>
            </div>
        </div>
        @if(!$usage['beds']['unlimited'])
            <div class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" 
                         style="width: {{ ($usage['beds']['current'] / $usage['beds']['limit']) * 100 }}%">
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Guardias (mes actual) --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Guardias (mes)</p>
                <p class="text-2xl font-bold">
                    {{ $usage['guardias']['current'] }}
                    @if(!$usage['guardias']['unlimited'])
                        / {{ $usage['guardias']['limit'] }}
                    @endif
                </p>
            </div>
            <div class="text-purple-500">
                <i class="fas fa-calendar-alt text-3xl"></i>
            </div>
        </div>
        @if(!$usage['guardias']['unlimited'])
            <div class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-600 h-2 rounded-full" 
                         style="width: {{ ($usage['guardias']['current'] / $usage['guardias']['limit']) * 100 }}%">
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Storage --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Almacenamiento</p>
                <p class="text-2xl font-bold">
                    {{ number_format($usage['storage_mb']['current'], 0) }} MB
                    @if(!$usage['storage_mb']['unlimited'])
                        / {{ $usage['storage_mb']['limit'] }} MB
                    @endif
                </p>
            </div>
            <div class="text-orange-500">
                <i class="fas fa-hdd text-3xl"></i>
            </div>
        </div>
        @if(!$usage['storage_mb']['unlimited'])
            <div class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-orange-600 h-2 rounded-full" 
                         style="width: {{ ($usage['storage_mb']['current'] / $usage['storage_mb']['limit']) * 100 }}%">
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
```

## Pasos para Completar la Implementación

### En Staging:

1. **Ejecutar migración de planes** (si no se ha hecho):
   ```bash
   php artisan migrate
   php artisan optimize:clear
   ```

2. **Aplicar límites en controladores** siguiendo los ejemplos arriba en:
   - UserController o SystemUserController (usuarios)
   - BedController o CamaController (camas)
   - AdministradorController (guardias)

3. **Crear vista de error 403** en `resources/views/errors/feature-disabled.blade.php`

4. **Agregar widget de uso** en dashboard o configuración del tenant

5. **Probar cada módulo**:
   - Intentar acceder a módulo deshabilitado → debe mostrar 403
   - Intentar crear recurso al límite → debe mostrar error
   - Verificar que límites null funcionan como ilimitado

## Archivos Modificados en Esta Implementación

### Nuevos:
- `app/Http/Middleware/EnsureTenantFeatureEnabled.php`
- `app/Services/TenantPlanLimitService.php`
- `IMPLEMENTATION_GUIDE.md` (este archivo)

### Modificados:
- `bootstrap/app.php` (alias tenant.feature)
- `routes/app.php` (middleware en 5 módulos)

## Riesgos y Consideraciones

### ⚠️ Riesgos:
1. **Tenants sin plan asignado**: El servicio usa valores restrictivos por defecto (plan básico)
2. **Migración en producción**: Asegurar que `plan_id` esté poblado o que el fallback a `plan` slug funcione
3. **Performance**: El cálculo de storage puede ser lento en tenants con muchos archivos

### ✅ Mitigaciones:
1. El servicio tiene fallback robusto a plan básico
2. Los límites null se manejan correctamente como ilimitado
3. El middleware solo se aplica en contexto tenant
4. Los mensajes de error son claros y amigables

## Testing en Staging

### Checklist de Pruebas:

- [ ] Acceder a módulo inventario con plan que NO lo incluye → 403
- [ ] Acceder a módulo inventario con plan que SÍ lo incluye → funciona
- [ ] Crear usuario cuando se alcanzó el límite → error claro
- [ ] Crear usuario cuando hay capacidad → funciona
- [ ] Crear cama cuando se alcanzó el límite → error claro
- [ ] Crear cama cuando hay capacidad → funciona
- [ ] Crear guardia cuando se alcanzó límite mensual → error claro
- [ ] Crear guardia cuando hay capacidad → funciona
- [ ] Verificar que tenant sin plan asignado usa límites básicos
- [ ] Verificar que límites null funcionan como ilimitado
- [ ] Verificar widget de uso muestra datos correctos
