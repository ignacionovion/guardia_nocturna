# INSTRUCCIONES PARA DIAGNÓSTICO DE FLUJO DE COOKIES EN NAVEGADOR

## 🎯 OBJETIVO

Identificar exactamente qué pasa con la cookie de sesión entre el login y el dashboard, y por qué el navegador entra en bucle mientras curl no.

---

## 📋 PREPARACIÓN

### 1. Hacer git pull en staging y limpiar caché
```bash
cd /ruta/al/proyecto
git pull origin main
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 2. Verificar que los cambios de logging están aplicados
Los siguientes archivos ahora tienen logging detallado de sesión/cookies:
- `app/Http/Controllers/AuthController.php`
- `app/Http/Middleware/Authenticate.php`
- `app/Http/Middleware/RedirectIfAuthenticated.php`

---

## 🧪 PRUEBA EN NAVEGADOR (Firefox)

### Paso 1: Abrir DevTools
1. Abrir Firefox
2. Presionar F12 para abrir DevTools
3. Ir a la pestaña **Network**
4. Activar **Persist Logs** (para que no se borren los logs entre redirects)
5. Limpiar cookies existentes:
   - Ir a **Storage** → **Cookies** → `http://octava-temuco.dev-app.cl`
   - Borrar todas las cookies

### Paso 2: Acceder a la página de login
1. En el navegador, ir a: `http://octava-temuco.dev-app.cl/`
2. En DevTools → Network, buscar el request `GET /`
3. **Documentar:**
   - Status code: (debería ser 200)
   - Response Headers → buscar `Set-Cookie`
   - Copiar el valor completo de `Set-Cookie: guardiapp-staging-session=...`
   - Copiar el valor completo de `Set-Cookie: XSRF-TOKEN=...`
   - Anotar el `domain=` de ambas cookies

### Paso 3: Hacer login
1. Ingresar credenciales válidas (usuario: `admin`, password: `password` o las que correspondan)
2. Click en "Iniciar Sesión"
3. **IMPORTANTE:** El navegador probablemente entrará en bucle aquí
4. **NO cerrar DevTools**, dejar que el bucle corra por unos segundos
5. Detener el bucle cerrando la pestaña o presionando ESC

### Paso 4: Analizar el flujo en DevTools → Network

Buscar la secuencia de requests en orden cronológico:

#### Request 1: `POST /` (login)
**Documentar:**
- Status code: (debería ser 302)
- Request Headers → Cookie: (copiar valor completo)
- Response Headers → Set-Cookie: (copiar valor completo de ambas cookies)
- Response Headers → Location: (debería ser `/dashboard`)
- **¿La cookie de sesión cambió?** Comparar con la cookie del GET / inicial

#### Request 2: `GET /dashboard` (primer redirect)
**Documentar:**
- Status code: (¿302 o 200?)
- Request Headers → Cookie: (copiar valor completo)
- **¿El navegador envió la cookie de sesión del POST /?** Comparar
- Response Headers → Set-Cookie: (si existe, copiar)
- Response Headers → Location: (si es 302, copiar)

#### Request 3: `GET /` (si hubo redirect de /dashboard)
**Documentar:**
- Status code: (¿302 o 200?)
- Request Headers → Cookie: (copiar valor completo)
- **¿El navegador envió la misma cookie?**
- Response Headers → Set-Cookie: (si existe, copiar)
- Response Headers → Location: (si es 302, copiar)

#### Request 4+: Siguientes requests del bucle
**Documentar:**
- ¿Cuál es el patrón? `/dashboard` → `/` → `/dashboard` → `/`?
- ¿La cookie de sesión cambia en cada request?
- ¿O siempre es la misma cookie?

---

## 📊 ANÁLISIS DE COOKIES EN DevTools

### Ir a Storage → Cookies → `http://octava-temuco.dev-app.cl`

**Documentar:**
- Nombre de la cookie de sesión: (debería ser `guardiapp-staging-session`)
- Valor: (copiar primeros 50 caracteres)
- Domain: (debería ser `.dev-app.cl`)
- Path: (debería ser `/`)
- Expires / Max-Age: (fecha/tiempo)
- HttpOnly: (debería ser ✓)
- Secure: (¿está marcado?)
- SameSite: (debería ser `Lax`)

**Preguntas clave:**
1. ¿La cookie existe después del POST login?
2. ¿El Domain es `.dev-app.cl` (con punto inicial)?
3. ¿Secure está marcado? (si el sitio es HTTP, NO debería estar marcado)

---

## 📝 REVISAR LOGS DE LARAVEL

En el servidor de staging, revisar los logs:

```bash
tail -f storage/logs/laravel.log
```

O si los logs son muy largos:
```bash
tail -200 storage/logs/laravel.log | grep "==="
```

**Buscar las siguientes líneas en orden:**

### 1. AuthController@login START
```
=== AuthController@login START ===
{
  "session_id_before": "...",
  "cookies_received": {...}
}
```

### 2. AuthController@login SUCCESS
```
=== AuthController@login SUCCESS ===
{
  "session_id_after_regenerate": "...",
  "auth_check": true/false,
  "session_data": {...}
}
```

### 3. AuthController@login RESPONSE
```
=== AuthController@login RESPONSE ===
{
  "session_id": "...",
  "response_cookies": [...]
}
```

### 4. Authenticate Middleware (en GET /dashboard)
```
=== Authenticate Middleware ===
{
  "url": "http://octava-temuco.dev-app.cl/dashboard",
  "session_id": "...",
  "auth_check": true/false,
  "auth_user_id": ...,
  "cookies_received": [...]
}
```

### 5. RedirectIfAuthenticated Middleware (en GET /)
```
=== RedirectIfAuthenticated Middleware (guest) ===
{
  "url": "http://octava-temuco.dev-app.cl/",
  "session_id": "...",
  "is_authenticated": true/false,
  "cookies_received": [...]
}
```

---

## 🎯 PREGUNTAS CLAVE A RESPONDER

### Del flujo en DevTools:

1. **POST / (login):**
   - ¿Devuelve 302 con Location: /dashboard?
   - ¿Emite Set-Cookie con nueva sesión?
   - ¿El session_id cambia respecto al GET / inicial?

2. **GET /dashboard (primer redirect):**
   - ¿El navegador ENVÍA la cookie de sesión del POST /?
   - ¿Devuelve 302 o 200?
   - Si devuelve 302, ¿a dónde redirige?

3. **GET / (segundo redirect, si existe):**
   - ¿El navegador ENVÍA la misma cookie?
   - ¿Devuelve 302 o 200?
   - Si devuelve 302, ¿a dónde redirige?

4. **Bucle:**
   - ¿Cuál es el patrón exacto? `/dashboard` ↔ `/`?
   - ¿La cookie cambia en cada request o es siempre la misma?

### De los logs de Laravel:

1. **En AuthController@login SUCCESS:**
   - ¿`auth_check` es `true`?
   - ¿`session_id_after_regenerate` tiene un valor?
   - ¿`session_data` contiene datos de autenticación?

2. **En Authenticate Middleware (GET /dashboard):**
   - ¿`session_id` es el MISMO que en AuthController@login RESPONSE?
   - ¿`auth_check` es `true` o `false`?
   - ¿`auth_user_id` tiene un valor o es `null`?
   - ¿`cookies_received` incluye `guardiapp-staging-session`?

3. **En RedirectIfAuthenticated Middleware (GET /):**
   - ¿`is_authenticated` es `true` o `false`?
   - ¿Por qué está redirigiendo si el usuario NO está autenticado?

---

## 📋 FORMATO DE REPORTE

Por favor, documentar en este formato:

```
=== FLUJO EN DEVTOOLS ===

1. GET / (inicial)
   Status: 200
   Set-Cookie: guardiapp-staging-session=ABC123...; domain=.dev-app.cl
   Set-Cookie: XSRF-TOKEN=XYZ789...; domain=.dev-app.cl

2. POST / (login)
   Status: 302
   Request Cookie: guardiapp-staging-session=ABC123...
   Response Set-Cookie: guardiapp-staging-session=DEF456...; domain=.dev-app.cl
   Response Location: /dashboard
   ¿Cookie cambió?: SÍ (ABC123 → DEF456)

3. GET /dashboard (redirect)
   Status: 302 o 200
   Request Cookie: guardiapp-staging-session=??? (¿DEF456 o ABC123 o ninguna?)
   Response Location: / (si es 302)
   
4. GET / (segundo redirect)
   Status: 302 o 200
   Request Cookie: guardiapp-staging-session=???
   Response Location: /dashboard (si es 302)

5. Patrón del bucle:
   /dashboard (302 → /) → / (302 → /dashboard) → /dashboard (302 → /) → ...
   Cookie en cada request: ¿siempre la misma o cambia?

=== COOKIES EN STORAGE ===

Nombre: guardiapp-staging-session
Valor: DEF456... (primeros 50 chars)
Domain: .dev-app.cl
Path: /
Secure: ✓ o ✗
SameSite: Lax

=== LOGS DE LARAVEL ===

AuthController@login SUCCESS:
  session_id_after_regenerate: "sess_12345"
  auth_check: true
  
Authenticate Middleware (GET /dashboard):
  session_id: "sess_12345" o "sess_XXXXX" (¿diferente?)
  auth_check: true o false
  auth_user_id: 1 o null
  cookies_received: ["guardiapp-staging-session", "XSRF-TOKEN"] o solo ["XSRF-TOKEN"]

RedirectIfAuthenticated (GET /):
  is_authenticated: true o false
  (si es true, explica por qué redirige a /dashboard)
```

---

## 🔍 CAUSA RAÍZ ESPERADA

Basándome en el diagnóstico, la causa raíz será UNA de estas:

### A. Cookie no se envía en GET /dashboard
- **Síntoma:** Request Cookie en GET /dashboard está vacío o no incluye `guardiapp-staging-session`
- **Causa:** Configuración de cookie (Secure, SameSite, Domain) incompatible con el navegador
- **Solución:** Ajustar SESSION_SECURE_COOKIE o SESSION_SAME_SITE

### B. Session ID cambia entre POST / y GET /dashboard
- **Síntoma:** session_id en logs es diferente entre AuthController y Authenticate
- **Causa:** Algún middleware está regenerando la sesión otra vez
- **Solución:** Identificar y eliminar la regeneración duplicada

### C. Sesión se guarda pero no se lee
- **Síntoma:** Cookie se envía correctamente, session_id es el mismo, pero auth_check es false
- **Causa:** Archivo de sesión no se escribe o no se lee (permisos de storage)
- **Solución:** Verificar permisos de storage/framework/sessions

### D. Middleware guest detecta autenticación fantasma
- **Síntoma:** En GET /, RedirectIfAuthenticated dice is_authenticated=true pero Authenticate dice auth_check=false
- **Causa:** Guards diferentes o sesión inconsistente
- **Solución:** Asegurar que ambos middlewares usan el mismo guard

---

Una vez tengas toda esta información, podremos identificar la causa raíz exacta y aplicar la solución correcta.
