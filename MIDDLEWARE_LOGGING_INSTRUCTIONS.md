# 🔍 INSTRUCCIONES PARA IDENTIFICAR MIDDLEWARE RESPONSABLE DEL LOOP

## ✅ LOGGING AGREGADO

He agregado logging detallado con emojis de colores en los 4 middlewares clave:

- 🔴 **Authenticate** - Redirige a `/` si NO autenticado
- 🟢 **RedirectIfAuthenticated (guest)** - Redirige a `/dashboard` si SÍ autenticado
- 🟡 **EnsureActiveGuardia** - Valida que guardia esté activa, redirige a `/` + logout
- 🔵 **EnsureGuardiaOnDuty** - Valida que guardia esté de turno, redirige a `guardia.off_duty`

---

## 📋 PASOS PARA DIAGNOSTICAR

### 1. Aplicar cambios en staging

```bash
cd /ruta/al/proyecto
git pull origin main
php artisan optimize:clear
```

### 2. Limpiar logs

```bash
# Vaciar el log para tener solo el flujo del login
> storage/logs/laravel.log
```

O si no tienes acceso directo:
```bash
tail -f storage/logs/laravel.log > /tmp/login_flow.log
```

### 3. Hacer login en el navegador

1. Ir a `http://octava-temuco.dev-app.cl/`
2. Ingresar credenciales válidas
3. Click "Iniciar Sesión"
4. Esperar 5 segundos (para que el loop se ejecute varias veces)
5. Cerrar la pestaña o presionar ESC

### 4. Revisar logs

```bash
cat storage/logs/laravel.log | grep "==="
```

O con colores:
```bash
cat storage/logs/laravel.log | grep -E "🔴|🟢|🟡|🔵"
```

---

## 🎯 QUÉ BUSCAR EN LOS LOGS

### **Flujo esperado (CORRECTO):**

```
POST /
(sin middlewares, solo AuthController)

GET /dashboard
🔴 Authenticate: auth_check=true, ALLOWING
🔵 EnsureGuardiaOnDuty: user_role=guardia, ALLOWING
(200 OK)
```

### **Flujo con loop (INCORRECTO):**

```
POST /
(sin middlewares, solo AuthController)

GET /dashboard
🔴 Authenticate: auth_check=false, REDIRECTING to /
(302 → /)

GET /
🟢 RedirectIfAuthenticated: is_authenticated=true, REDIRECTING to /dashboard
(302 → /dashboard)

GET /dashboard
🔴 Authenticate: auth_check=false, REDIRECTING to /
(302 → /)

... LOOP INFINITO
```

---

## 🔍 PREGUNTAS CLAVE A RESPONDER

### 1. En el primer GET /dashboard después del login:

**¿Qué dice el middleware Authenticate (🔴)?**
- `auth_check=true` → Usuario SÍ está autenticado → ALLOWING
- `auth_check=false` → Usuario NO está autenticado → REDIRECTING to /

**Si auth_check=false:**
- ¿El `session_id` es el mismo que en el POST login?
- ¿El `user_id` es null?
- **Causa:** Sesión no persiste entre POST y GET

### 2. En el GET / (si hay redirect):

**¿Qué dice el middleware RedirectIfAuthenticated (🟢)?**
- `is_authenticated=true` → Usuario SÍ está autenticado → REDIRECTING to /dashboard
- `is_authenticated=false` → Usuario NO está autenticado → ALLOWING

**Si is_authenticated=true:**
- ¿Por qué Authenticate dice `auth_check=false` pero RedirectIfAuthenticated dice `is_authenticated=true`?
- **Causa:** Inconsistencia entre guards o sesión

### 3. ¿Algún otro middleware está redirigiendo?

**¿Aparece EnsureActiveGuardia (🟡)?**
- Si aparece y dice "REDIRECTING to / + LOGOUT"
- **Causa:** Guardia no tiene `guardia_id` o guardia no está activa
- **Efecto:** Hace logout y regenera sesión → pierde autenticación

**¿Aparece EnsureGuardiaOnDuty (🔵)?**
- Si aparece y dice "REDIRECTING to guardia.off_duty"
- **Causa:** Guardia no está de turno
- **Efecto:** Redirige a página de "fuera de servicio"

---

## 📊 FORMATO DE REPORTE

Por favor, copia y pega los logs completos del flujo, especialmente:

```
=== PRIMER GET /dashboard ===
🔴 Authenticate: {
  path: "dashboard",
  session_id: "...",
  auth_check: true/false,
  auth_user_id: ...,
  auth_user_role: "...",
  tenant_id: "..."
}
🔴 Authenticate: REDIRECTING to / (o ALLOWING)

=== GET / (si hay redirect) ===
🟢 RedirectIfAuthenticated: {
  path: "/",
  session_id: "...",
  is_authenticated: true/false,
  user_id: ...,
  user_role: "..."
}
🟢 RedirectIfAuthenticated: REDIRECTING to /dashboard (o ALLOWING)

=== SEGUNDO GET /dashboard (si hay loop) ===
🔴 Authenticate: {
  session_id: "...",
  auth_check: true/false,
  ...
}
```

---

## 🎯 CAUSAS POSIBLES

### **A. Authenticate dice auth_check=false en GET /dashboard**

**Síntoma:**
```
GET /dashboard
🔴 Authenticate: auth_check=false, REDIRECTING to /
```

**Causas posibles:**
1. Session ID cambió entre POST y GET
2. Archivo de sesión no se escribió (permisos)
3. Sesión se invalidó por otro middleware

**Solución:**
- Verificar que `session_id` sea el mismo
- Verificar permisos de `storage/framework/sessions`
- Verificar que ningún middleware haga logout

---

### **B. EnsureActiveGuardia hace logout**

**Síntoma:**
```
GET /dashboard
🟡 EnsureActiveGuardia: REDIRECTING to / + LOGOUT
```

**Causas posibles:**
1. Usuario guardia no tiene `guardia_id`
2. Guardia no está activa (`is_active_week=false`)

**Solución:**
- Verificar en la BD que el usuario tenga `guardia_id`
- Verificar que la guardia tenga `is_active_week=true`

---

### **C. EnsureGuardiaOnDuty redirige a off_duty**

**Síntoma:**
```
GET /dashboard
🔵 EnsureGuardiaOnDuty: REDIRECTING to guardia.off_duty
```

**Causa:**
- Guardia no está de turno según el calendario

**Solución:**
- Verificar calendario de guardias
- O remover temporalmente el middleware para testing

---

### **D. RedirectIfAuthenticated detecta autenticación cuando Authenticate no**

**Síntoma:**
```
GET /dashboard
🔴 Authenticate: auth_check=false, REDIRECTING to /

GET /
🟢 RedirectIfAuthenticated: is_authenticated=true, REDIRECTING to /dashboard
```

**Causa:**
- Inconsistencia entre guards
- Sesión existe pero está vacía

**Solución:**
- Verificar que ambos usen el mismo guard (`web`)
- Verificar que la sesión contenga datos de autenticación

---

Una vez que tengas los logs, podremos identificar exactamente cuál middleware está causando el loop y por qué.
