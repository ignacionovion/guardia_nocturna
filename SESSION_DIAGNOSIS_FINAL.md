# DIAGNÓSTICO FINAL: BUCLE DE REDIRECCIÓN Y SESIÓN

## 📊 PROBLEMA IDENTIFICADO

**Síntoma:** Bucle de redirección infinito entre `/` y `/dashboard` después de cambiar `SESSION_DRIVER=file`.

**Causa raíz:** La sesión NO persiste entre requests, causando que:
1. Login funciona dentro del request (auth_check = true)
2. Redirect a `/dashboard` → Nueva request
3. Sesión se pierde → auth_check = false
4. Middleware `auth` redirige a `/`
5. Middleware `guest` detecta estado inconsistente → redirige a `/dashboard`
6. **BUCLE INFINITO**

---

## 🔍 CADENA DE REDIRECCIÓN EXACTA (curl)

### Sin autenticación:
```bash
GET /dashboard → 302 Found → Location: http://octava-temuco.dev-app.cl
GET / → 200 OK (login page)
```

### Con login (pero sesión perdida):
```
POST / (login) → auth_check = true → redirect('/dashboard')
GET /dashboard → auth_check = false → redirect('/') [Authenticate.php:22]
GET / → guest middleware detecta cookie → redirect('/dashboard') [RedirectIfAuthenticated.php:29]
GET /dashboard → auth_check = false → redirect('/') [Authenticate.php:22]
... BUCLE INFINITO
```

---

## 📁 ARCHIVOS RESPONSABLES

### 1. Redirect `/dashboard` → `/` (usuario no autenticado)
- **Archivo:** `app/Http/Middleware/Authenticate.php`
- **Línea:** 22
- **Código:** `return '/';`

### 2. Redirect `/` → `/dashboard` (usuario autenticado)
- **Archivo:** `app/Http/Middleware/RedirectIfAuthenticated.php`
- **Línea:** 29
- **Código:** `return redirect('/dashboard');`

### 3. Middleware NO registrado en bootstrap/app.php
- **Archivo:** `bootstrap/app.php`
- **Problema:** NO hay alias `'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class`
- **Consecuencia:** Laravel usa el middleware por defecto de Illuminate, que puede comportarse diferente

---

## 🎯 CAUSA RAÍZ: SESSION_DRIVER=file NO FUNCIONA

**Por qué `SESSION_DRIVER=file` tampoco resuelve el problema:**

### Opción A: Permisos de storage
```bash
# Verificar permisos actuales
ls -la storage/framework/sessions

# Si no existe o tiene permisos incorrectos:
mkdir -p storage/framework/sessions
chmod -R 775 storage/framework/sessions
chown -R www-data:www-data storage/framework/sessions
```

### Opción B: SESSION_DOMAIN incorrecto para multi-tenant
**Configuración actual:** `SESSION_DOMAIN=null`

**Problema:** En multi-tenant con subdominios `{tenant}.dev-app.cl`, la cookie debe ser compartida entre subdominios.

**Configuración correcta:**
```bash
SESSION_DOMAIN=.dev-app.cl
```

**Por qué:** El punto inicial `.dev-app.cl` permite que la cookie sea válida para:
- `octava-temuco.dev-app.cl`
- `cuarta-temuco.dev-app.cl`
- `sas.dev-app.cl`
- Cualquier otro subdominio

### Opción C: Middleware `guest` no registrado
**Problema:** Las rutas usan `->middleware('guest')` pero NO hay alias en `bootstrap/app.php`.

**Solución:** Registrar el middleware custom:
```php
$middleware->alias([
    'auth'  => \App\Http\Middleware\Authenticate::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    // ... resto de aliases
]);
```

---

## 🔧 SOLUCIÓN COMPLETA

### Paso 1: Registrar middleware `guest` en bootstrap/app.php

**Archivo:** `bootstrap/app.php`

**Cambio:**
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*');
    $middleware->alias([
        'auth'                => \App\Http\Middleware\Authenticate::class,
        'guest'               => \App\Http\Middleware\RedirectIfAuthenticated::class, // ← AGREGAR
        'super_admin'         => \App\Http\Middleware\EnsureSuperAdmin::class,
        // ... resto sin cambios
    ]);
})
```

### Paso 2: Configurar SESSION_DOMAIN para multi-tenant

**Archivo:** `.env` (en staging)

**Cambio:**
```bash
SESSION_DRIVER=file
SESSION_DOMAIN=.dev-app.cl
```

**Importante:** El punto inicial es crítico.

### Paso 3: Verificar permisos de storage

**En el servidor de staging:**
```bash
# Crear directorio si no existe
mkdir -p storage/framework/sessions

# Establecer permisos correctos
chmod -R 775 storage/framework/sessions
chown -R www-data:www-data storage/framework/sessions

# Verificar
ls -la storage/framework/sessions
```

### Paso 4: Limpiar caché

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### Paso 5: Probar login

Acceder a `http://octava-temuco.dev-app.cl/` y hacer login.

**Resultado esperado:**
- Login exitoso
- Redirect a `/dashboard`
- Dashboard se carga correctamente
- NO hay bucle de redirección
- Sesión persiste entre requests

---

## 📋 DIFF EXACTO

### `bootstrap/app.php`

```diff
     $middleware->alias([
         'auth'                => \App\Http\Middleware\Authenticate::class,
+        'guest'               => \App\Http\Middleware\RedirectIfAuthenticated::class,
         'super_admin'         => \App\Http\Middleware\EnsureSuperAdmin::class,
```

### `.env` (staging)

```diff
-SESSION_DRIVER=database
+SESSION_DRIVER=file
-SESSION_DOMAIN=null
+SESSION_DOMAIN=.dev-app.cl
```

---

## 🧪 VALIDACIÓN

### Test 1: Acceso directo a /dashboard sin login
```bash
curl -I http://octava-temuco.dev-app.cl/dashboard
```
**Esperado:** `302 Found` → `Location: http://octava-temuco.dev-app.cl`

### Test 2: Login y acceso a dashboard
1. Acceder a `http://octava-temuco.dev-app.cl/`
2. Hacer login con credenciales válidas
3. Verificar redirect a `/dashboard`
4. Verificar que dashboard carga correctamente
5. Verificar en DevTools → Application → Cookies:
   - Cookie `guardiapp-staging-session` existe
   - Domain = `.dev-app.cl`
   - Path = `/`
   - HttpOnly = true

### Test 3: Refresh en dashboard
1. Estando en `/dashboard` autenticado
2. Hacer F5 (refresh)
3. Verificar que dashboard se recarga correctamente
4. NO debe redirigir a login

---

## 📊 EXPLICACIÓN TÉCNICA

### Por qué SESSION_DOMAIN=.dev-app.cl es crítico

**Sin el punto inicial (SESSION_DOMAIN=dev-app.cl):**
- Cookie solo válida para `dev-app.cl`
- NO válida para `octava-temuco.dev-app.cl`
- Sesión no se comparte entre subdominios

**Con el punto inicial (SESSION_DOMAIN=.dev-app.cl):**
- Cookie válida para `*.dev-app.cl`
- Válida para todos los subdominios
- Sesión se comparte correctamente

### Por qué registrar middleware `guest` es importante

**Sin alias:**
- Laravel usa `Illuminate\Auth\Middleware\RedirectIfAuthenticated` por defecto
- Puede tener comportamiento diferente al custom
- Puede causar redirects inesperados

**Con alias:**
- Laravel usa `App\Http\Middleware\RedirectIfAuthenticated` custom
- Comportamiento controlado y predecible
- Redirect a `/dashboard` para tenant, `/admin` para central

---

## 🎯 PRÓXIMOS PASOS

1. Aplicar cambios en `bootstrap/app.php` (agregar alias `guest`)
2. Aplicar cambios en `.env` staging (SESSION_DOMAIN=.dev-app.cl)
3. Verificar permisos de `storage/framework/sessions`
4. Limpiar caché
5. Probar login
6. Confirmar funcionamiento
7. Quitar logs temporales de `AuthController` y `TableroController`
8. Commit y push final
