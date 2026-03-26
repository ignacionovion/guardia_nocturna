# INSTRUCCIONES PARA CORREGIR PERSISTENCIA DE SESIÓN

## PROBLEMA IDENTIFICADO

La sesión NO persiste entre el POST login y el GET /dashboard.

**Evidencia:**
- Login (post-attempt): `session_id = SESSION_ID_A`, `auth_check = true`
- Dashboard: `session_id = SESSION_ID_B`, `auth_check = false`

**Causa raíz:**
`SESSION_DRIVER=database` en multi-tenant causa que las sesiones se guarden en la BD del tenant, lo cual puede fallar por:
1. Tabla `sessions` no existe en BD del tenant
2. Contexto de tenancy se pierde entre requests
3. Conexión de sesión no está configurada para usar BD central

---

## SOLUCIÓN TEMPORAL (PRUEBA)

### Paso 1: Cambiar SESSION_DRIVER a file

En el servidor de staging, editar el archivo `.env`:

```bash
SESSION_DRIVER=file
```

### Paso 2: Limpiar caché

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Paso 3: Verificar permisos de storage

```bash
chmod -R 775 storage/framework/sessions
chown -R www-data:www-data storage/framework/sessions
```

### Paso 4: Probar login

Acceder a `http://cuarta-temuco.sas.dev-app.cl/` y hacer login.

**Resultado esperado:**
- El `dd()` en `TableroController@index` debe mostrar:
  - `auth_check = true`
  - `auth_user_id = 1`
  - `session_id` puede ser diferente, pero la autenticación debe persistir

---

## SOLUCIÓN DEFINITIVA (SI FILE FUNCIONA)

Si `SESSION_DRIVER=file` resuelve el problema, entonces la causa es la configuración de sesión en BD.

### Opción A: Mantener SESSION_DRIVER=file (Recomendado para multi-tenant)

**Ventajas:**
- Simple
- No depende de BD
- Funciona bien con multi-tenancy
- Rendimiento adecuado para aplicaciones pequeñas/medianas

**Configuración final en `.env`:**
```bash
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
```

### Opción B: Usar SESSION_DRIVER=database con conexión central

Si prefieres usar BD para sesiones (para balanceo de carga futuro), debes forzar que las sesiones se guarden en la BD central, NO en la del tenant.

**Configuración en `.env`:**
```bash
SESSION_DRIVER=database
SESSION_CONNECTION=central
SESSION_TABLE=sessions
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
```

**Migración necesaria:**
```bash
# En la BD central (NO en tenant)
php artisan session:table
php artisan migrate --path=database/migrations/xxxx_create_sessions_table.php
```

---

## SOLUCIÓN DEFINITIVA (SI FILE NO FUNCIONA)

Si `SESSION_DRIVER=file` tampoco funciona, entonces el problema es de cookies.

### Verificar configuración de cookies

**En `.env`:**
```bash
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false  # Si staging usa HTTP
SESSION_SAME_SITE=lax
```

**Si staging usa HTTPS:**
```bash
SESSION_SECURE_COOKIE=true
```

### Verificar que la cookie se está enviando

En el navegador (DevTools → Network):
1. POST `/` (login) → Response Headers → Buscar `Set-Cookie: laravel_session=...`
2. GET `/dashboard` → Request Headers → Buscar `Cookie: laravel_session=...`

Si la cookie NO aparece en el GET, el problema es:
- `SESSION_DOMAIN` incorrecto
- `SESSION_SECURE_COOKIE` incorrecto (HTTPS/HTTP mismatch)
- `SESSION_SAME_SITE` bloqueando la cookie

---

## ARCHIVO RESPONSABLE

`config/session.php` - Configuración de sesión de Laravel

**Líneas clave:**
- Línea 21: `'driver' => env('SESSION_DRIVER', 'database')`
- Línea 76: `'connection' => env('SESSION_CONNECTION')`
- Línea 89: `'table' => env('SESSION_TABLE', 'sessions')`
- Línea 159: `'domain' => env('SESSION_DOMAIN')`
- Línea 172: `'secure' => env('SESSION_SECURE_COOKIE')`

---

## PRÓXIMOS PASOS

1. Aplicar solución temporal (SESSION_DRIVER=file)
2. Probar login
3. Si funciona → Aplicar solución definitiva (Opción A o B)
4. Si no funciona → Revisar cookies en DevTools
5. Quitar los `dd()` temporales de AuthController y TableroController
6. Confirmar funcionamiento final
