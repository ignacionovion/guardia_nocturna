# 🚀 Instrucciones de Deploy - Phase 4 Modal Fixes

## ⚠️ IMPORTANTE: Ejecutar en Servidor Staging

Después de hacer `git pull` en el servidor staging, **DEBES ejecutar estos comandos**:

```bash
# 1. Limpiar cache de rutas
php artisan route:clear

# 2. Limpiar cache de configuración
php artisan config:clear

# 3. Limpiar cache de vistas
php artisan view:clear

# 4. Limpiar todo el cache de optimización
php artisan optimize:clear

# 5. Verificar que las rutas existen
php artisan route:list | grep api
```

## 📋 Verificación de Rutas

Después de ejecutar los comandos, verifica que estas rutas existan:

```
GET /api/bomberos
GET /api/emergency-keys  
GET /api/emergency-units
```

## 🧪 Testing en Navegador

1. Abrir DevTools (F12) → Console
2. Refrescar página (Ctrl+R o Cmd+R)
3. Abrir modal de Reemplazo/Refuerzo/Emergencias
4. Verificar en console:
   - ✅ Status 200 (OK)
   - ✅ Data received con array de datos
   - ❌ NO debe haber 404 ni 500

## 🐛 Si Persisten Errores

### Error 404 Not Found
- Causa: Cache de rutas no limpiado
- Solución: `php artisan route:clear && php artisan optimize:clear`

### Error 500 Internal Server Error
- Causa: Error en controlador o modelo
- Solución: Revisar logs en `storage/logs/tenants/cuarta-temuco.log`
- Comando: `tail -f storage/logs/tenants/cuarta-temuco.log`

### Error 403 Forbidden
- Causa: Middleware bloqueando acceso
- Solución: Verificar que usuario esté autenticado

## 📝 Logs de Debugging

Los modales ahora tienen logging detallado en la consola del navegador:

```
[ReemplazoModal] Fetching firefighters...
[ReemplazoModal] Response: 200 true
[ReemplazoModal] Data received: [...]
[ReemplazoModal] Firefighters loaded: X

[EmergenciasModal] Fetching data...
[EmergenciasModal] Keys response: 200 true
[EmergenciasModal] Units response: 200 true

[RefuerzoModal] Fetching firefighters...
[RefuerzoModal] Response: 200 true
```

## ✅ Checklist de Deploy

- [ ] `git pull` en servidor staging
- [ ] `php artisan route:clear`
- [ ] `php artisan optimize:clear`
- [ ] Verificar rutas con `php artisan route:list | grep api`
- [ ] Refrescar navegador con Ctrl+Shift+R (hard refresh)
- [ ] Abrir console y probar modales
- [ ] Verificar que aparecen datos en los selects

---

**Última actualización:** 16 Mar 2026 10:30 AM
**Branch:** feature/guardia-live-vue
**Commit:** 5d3063c
