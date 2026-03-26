# DIAGNÓSTICO EXACTO DEL BUCLE DE REDIRECCIÓN

## 📊 RESULTADOS DE curl EN STAGING

### Test 1: `curl -I http://octava-temuco.dev-app.cl/`
```
HTTP/1.1 200 OK
Set-Cookie: XSRF-TOKEN=...
Set-Cookie: guardiapp-staging-session=...; domain=.dev-app.cl
```
**Resultado:** `/` devuelve 200 OK (login page) - NO hay redirect

---

### Test 2: `curl -I http://octava-temuco.dev-app.cl/dashboard`
```
HTTP/1.1 302 Found
Location: http://octava-temuco.dev-app.cl
Set-Cookie: XSRF-TOKEN=...
Set-Cookie: guardiapp-staging-session=...; domain=.dev-app.cl
```
**Resultado:** `/dashboard` → 302 → `/` (redirect porque usuario NO autenticado)

---

### Test 3: `curl -IL http://octava-temuco.dev-app.cl/dashboard`
```
HTTP/1.1 302 Found
Location: http://octava-temuco.dev-app.cl

HTTP/1.1 200 OK
(login page HTML)
```
**Resultado:** `/dashboard` → `/` → 200 OK (NO hay bucle en esta cadena)

---

### Test 4: `curl -v -c cookies.txt -b cookies.txt -L http://octava-temuco.dev-app.cl/dashboard`
```
HTTP/1.1 200 OK
(login page HTML)
```
**Resultado:** Con cookies persistidas, `/dashboard` devuelve directamente la página de login (200 OK)

**IMPORTANTE:** NO se detecta bucle infinito con curl. El bucle solo ocurre en el navegador.

---

## 🔍 CADENA EXACTA DE REDIRECCIONES

### Sin autenticación (curl):
```
GET /dashboard → 302 → Location: http://octava-temuco.dev-app.cl
GET / → 200 OK (login page)
```

### Con navegador (Firefox):
```
GET /dashboard → 302 → /
GET / → ??? → /dashboard (AQUÍ ESTÁ EL PROBLEMA)
GET /dashboard → 302 → /
... BUCLE INFINITO
```

---

## 📁 ARCHIVOS RESPONSABLES DE LOS REDIRECTS

### 1. `/dashboard` → `/` (usuario NO autenticado)
**Archivo:** `app/Http/Middleware/Authenticate.php`
**Línea:** 22
**Código:**
```php
protected function redirectTo($request): ?string
{
    if (!$request->expectsJson()) {
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        if (in_array($host, $centralDomains, true)) {
            return '/login'; // central
        }

        return '/'; // tenant ← REDIRIGE A /
    }

    return null;
}
```

---

### 2. `/` → `/dashboard` (usuario SÍ autenticado)
**Archivo:** `app/Http/Middleware/RedirectIfAuthenticated.php`
**Línea:** 29
**Código:**
```php
public function handle(Request $request, Closure $next, string ...$guards): Response
{
    $guards = empty($guards) ? [null] : $guards;

    foreach ($guards as $guard) {
        if (Auth::guard($guard)->check()) {
            // Central guard → central dashboard
            if ($guard === 'central') {
                return redirect('/admin');
            }
            
            // Web guard (tenant) → tenant dashboard
            return redirect('/dashboard'); // ← REDIRIGE A /dashboard
        }
    }

    return $next($request);
}
```

---

### 3. Rutas con middleware `guest`
**Archivo:** `routes/app.php`
**Líneas:** 39-40
**Código:**
```php
Route::get('/', [AuthController::class, 'showLoginForm'])->name('tenant.login')->middleware('guest');
Route::post('/', [AuthController::class, 'login'])->middleware('guest');
```

**Middleware registrado en:** `bootstrap/app.php` línea 39
```php
'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
```

---

## 🎯 CAUSA RAÍZ DEL BUCLE

**El bucle NO se detecta con curl porque curl NO persiste el estado de autenticación entre requests.**

**El bucle SÍ ocurre en el navegador porque:**

1. Usuario hace login → POST `/`
2. `AuthController@login()` autentica al usuario
3. Sesión se guarda con `auth_check = true`
4. Redirect a `/dashboard`
5. **PROBLEMA:** La sesión NO persiste → `auth_check = false` en el siguiente request
6. Middleware `auth` detecta usuario NO autenticado → redirect a `/`
7. **PERO** la cookie de sesión SÍ existe (aunque vacía)
8. Middleware `guest` detecta... ¿qué?

**Hipótesis:** El middleware `guest` está detectando que el usuario SÍ está autenticado (por alguna razón) y redirige a `/dashboard`, causando el bucle.

---

## 🔬 OTROS REDIRECTS ENCONTRADOS EN EL CÓDIGO

### Redirects a `/dashboard`:
1. `app/Http/Middleware/RedirectIfAuthenticated.php:29` - Si usuario autenticado en ruta `guest`
2. `app/Http/Middleware/EnsureGuardiaOnDuty.php:72` - Si guardia está de turno y accede a `guardia.off_duty`
3. `app/Http/Controllers/GuardiaLiveController.php:25` - Si usuario no es guardia
4. `app/Http/Controllers/ImpersonateCallbackController.php:46` - Después de impersonar
5. `app/Http/Controllers/AuthController.php:86` - Después de login exitoso

### Redirects a `/`:
1. `app/Http/Middleware/Authenticate.php:22` - Si usuario NO autenticado (tenant)
2. `app/Http/Controllers/AuthController.php:106` - Después de logout
3. `app/Http/Middleware/EnsureActiveGuardia.php:28` - Si cuenta guardia sin guardia asociada
4. `app/Http/Middleware/EnsureActiveGuardia.php:37` - Si guardia no está activa

---

## 🧪 DIAGNÓSTICO PENDIENTE

**Para identificar exactamente dónde está el bucle, necesito:**

1. Verificar si el middleware `guest` está detectando autenticación cuando NO debería
2. Verificar si hay algún otro middleware que esté redirigiendo a `/dashboard`
3. Verificar si `EnsureGuardiaOnDuty` está causando un redirect adicional

**Sospecha principal:**

El middleware `EnsureGuardiaOnDuty` en la línea 72 redirige a `dashboard` si el guardia está de turno y accede a `guardia.off_duty`. 

**PERO** este middleware NO debería ejecutarse en la ruta `/` porque esa ruta NO tiene el middleware `guardia_on_duty`.

**Verificar:** ¿Hay algún middleware global que esté aplicando `guardia_on_duty` a todas las rutas?

---

## 📋 CONFIGURACIÓN DE MIDDLEWARES

### `bootstrap/app.php` - Middleware aliases (líneas 37-54):
```php
$middleware->alias([
    'auth'                => \App\Http\Middleware\Authenticate::class,
    'guest'               => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'super_admin'         => \App\Http\Middleware\EnsureSuperAdmin::class,
    'ensure_captain'      => \App\Http\Middleware\EnsureCaptain::class,
    'ensure_guardia'      => \App\Http\Middleware\EnsureGuardia::class,
    'ensure_active_guardia' => \App\Http\Middleware\EnsureActiveGuardia::class,
    'emergency_access'    => \App\Http\Middleware\EnsureEmergencyAccess::class,
    'inventory_access'    => \App\Http\Middleware\EnsureInventoryAccess::class,
    'inventario_only'     => \App\Http\Middleware\EnsureInventoryOnly::class,
    'preventivas_admin'   => \App\Http\Middleware\EnsurePreventivasAdmin::class,
    'guardia_on_duty'     => \App\Http\Middleware\EnsureGuardiaOnDuty::class,
    'role_permission'     => \App\Http\Middleware\CheckRolePermission::class,
    'feature'             => \App\Http\Middleware\EnforceFeatureFlag::class,
    'tenant.feature'      => \App\Http\Middleware\EnsureTenantFeatureEnabled::class,
    'max_users'           => \App\Http\Middleware\EnforceMaxUsers::class,
    'plan.limit'          => \App\Http\Middleware\EnforcePlanLimits::class,
]);
```

**NO hay middlewares globales aplicados al grupo `web`.**

---

## 🎯 CONCLUSIÓN

**Cadena exacta de redirecciones:**

### Para `/`:
```
GET / → 200 OK (si NO autenticado)
GET / → 302 → /dashboard (si SÍ autenticado, por middleware guest)
```

### Para `/dashboard`:
```
GET /dashboard → 302 → / (si NO autenticado, por middleware auth)
GET /dashboard → 200 OK (si SÍ autenticado)
```

**El bucle ocurre cuando:**
1. La sesión NO persiste después del login
2. El navegador intenta acceder a `/dashboard`
3. Middleware `auth` detecta NO autenticado → redirect a `/`
4. **PROBLEMA DESCONOCIDO:** Algo redirige de `/` a `/dashboard` nuevamente
5. Bucle infinito

**Archivo responsable del redirect `/dashboard` → `/`:**
- `app/Http/Middleware/Authenticate.php:22`

**Archivo responsable del redirect `/` → `/dashboard`:**
- `app/Http/Middleware/RedirectIfAuthenticated.php:29` (solo si usuario autenticado)

**PERO:** curl NO detecta el bucle, lo que significa que el problema está en cómo el navegador maneja las cookies/sesión, NO en la lógica de redirects del código.

**Próximo paso:** Verificar por qué la sesión NO persiste después del login, incluso con `SESSION_DRIVER=file` y `SESSION_DOMAIN=.dev-app.cl`.
