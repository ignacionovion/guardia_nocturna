#!/bin/bash
# =============================================================================
# SCRIPT DE DESPLIEGUE STAGING - GuardiAPP
# =============================================================================
# Ejecutar en el servidor después de git pull
# Uso: bash deploy-staging.sh
# =============================================================================

set -e

echo "=========================================="
echo "GUARDIAPP - DESPLIEGUE STAGING"
echo "=========================================="

# 1. Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "ERROR: No se encuentra artisan. Ejecutar desde la raíz del proyecto."
    exit 1
fi

# 2. Git pull
echo ""
echo "[1/8] Actualizando código desde repositorio..."
git fetch origin
git reset --hard origin/saas
echo "✓ Código actualizado"

# 3. Verificar integridad de dashboard.blade.php
echo ""
echo "[2/8] Verificando integridad de vistas críticas..."
DASHBOARD_LINES=$(wc -l < resources/views/dashboard.blade.php)
DASHBOARD_IF=$(grep -c "@if" resources/views/dashboard.blade.php || echo 0)
DASHBOARD_ENDIF=$(grep -c "@endif" resources/views/dashboard.blade.php || echo 0)

echo "   dashboard.blade.php: $DASHBOARD_LINES líneas, $DASHBOARD_IF @if, $DASHBOARD_ENDIF @endif"

if [ "$DASHBOARD_IF" != "$DASHBOARD_ENDIF" ]; then
    echo "ERROR: Desbalance en dashboard.blade.php (@if=$DASHBOARD_IF, @endif=$DASHBOARD_ENDIF)"
    exit 1
fi
echo "✓ Vistas verificadas"

# 4. Limpiar vistas compiladas
echo ""
echo "[3/8] Limpiando vistas compiladas..."
rm -rf storage/framework/views/*.php 2>/dev/null || true
php artisan view:clear
echo "✓ Vistas limpiadas"

# 5. Limpiar caché
echo ""
echo "[4/8] Limpiando caché de aplicación..."
php artisan cache:clear
echo "✓ Caché limpiada"

# 6. Limpiar configuración
echo ""
echo "[5/8] Limpiando caché de configuración..."
php artisan config:clear
echo "✓ Configuración limpiada"

# 7. Limpiar rutas
echo ""
echo "[6/8] Limpiando caché de rutas..."
php artisan route:clear
echo "✓ Rutas limpiadas"

# 8. Optimizar (opcional en staging)
echo ""
echo "[7/8] Optimizando aplicación..."
php artisan optimize:clear
echo "✓ Optimización completada"

# 9. Verificar permisos
echo ""
echo "[8/8] Verificando permisos..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo "✓ Permisos verificados"

echo ""
echo "=========================================="
echo "DESPLIEGUE COMPLETADO"
echo "=========================================="
echo ""
echo "Verificación final:"
echo "  - Dashboard: $DASHBOARD_LINES líneas"
echo "  - Balance @if/@endif: OK"
echo "  - Vistas compiladas: Limpiadas"
echo "  - Caché: Limpiada"
echo ""
