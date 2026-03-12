#!/bin/bash
# =============================================================================
# SCRIPT DE AISLAMIENTO BINARIO - DASHBOARD.BLADE.PHP
# =============================================================================
# Ejecutar en servidor: bash aislar-dashboard.sh
# =============================================================================

set -e

DASHBOARD="resources/views/dashboard.blade.php"
BACKUP="resources/views/dashboard.blade.php.backup.$(date +%s)"
MINIMO="dashboard-minimo.blade.php"

echo "=========================================="
echo "AISLAMIENTO BINARIO - DASHBOARD"
echo "=========================================="
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "artisan" ]; then
    echo "ERROR: No se encuentra artisan. Ejecutar desde raíz del proyecto."
    exit 1
fi

echo "[1/6] Backup del dashboard original..."
cp "$DASHBOARD" "$BACKUP"
echo "✓ Backup creado: $BACKUP"
echo ""

echo "[2/6] Reemplazando con versión mínima..."
# Copiar desde el archivo mínimo (debe existir en el mismo directorio)
if [ -f "$MINIMO" ]; then
    cp "$MINIMO" "$DASHBOARD"
else
    # Crear versión mínima inline si no existe el archivo
    cat > "$DASHBOARD" << 'EOFMINIMO'
@extends('layouts.modern')

@section('title', 'Dashboard - Prueba')
@section('page-title', 'Dashboard')

@section('content')
    <div class="p-8 text-center">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Dashboard Mínimo - PRUEBA</h1>
        <p class="mt-4 text-slate-600 dark:text-slate-400">Si ves este mensaje, el layout y la estructura base funcionan correctamente.</p>
        <div class="mt-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-lg">
            <p class="text-emerald-700 dark:text-emerald-300 font-medium">✓ Vista mínima cargando correctamente</p>
        </div>
    </div>
@endsection
EOFMINIMO
fi
echo "✓ Versión mínima instalada"
echo ""

echo "[3/6] Limpieza completa de caché..."
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan optimize:clear 2>/dev/null || true

# Limpiar vistas compiladas manualmente
rm -rf storage/framework/views/*.php 2>/dev/null || true
echo "✓ Caché limpiada"
echo ""

echo "[4/6] Reinicio de servicios..."
# Intentar reiniciar php-fpm (puede variar según el servidor)
sudo systemctl restart php8.4-fpm 2>/dev/null || \
sudo systemctl restart php8.3-fpm 2>/dev/null || \
sudo systemctl restart php8.2-fpm 2>/dev/null || \
sudo systemctl restart php-fpm 2>/dev/null || \
echo "  (No se pudo reiniciar php-fpm automáticamente - puede requerir sudo manual)"

# Reload nginx
sudo systemctl reload nginx 2>/dev/null || \
sudo systemctl reload apache2 2>/dev/null || \
echo "  (No se pudo recargar web server - puede requerir sudo manual)"
echo "✓ Servicios reiniciados (o requieren reinicio manual)"
echo ""

echo "[5/6] Verificación de compilación..."
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
try {
    \$compiler = app('blade.compiler');
    \$content = file_get_contents('$DASHBOARD');
    \$compiled = \$compiler->compileString(\$content);
    echo '✓ COMPILACIÓN BLADE: OK\n';
    exit(0);
} catch (Exception \$e) {
    echo '✗ COMPILACIÓN BLADE: ERROR\n';
    echo '  ' . \$e->getMessage() . '\n';
    exit(1);
}
" || true
echo ""

echo "=========================================="
echo "RESULTADO DE LA PRUEBA"
echo "=========================================="
echo ""
echo "INSTRUCCIONES:"
echo "1. Abre el navegador y visita: http://tercera-temuco.sas.dev-app.cl/dashboard"
echo "2. Observa el resultado:"
echo ""
echo "   SI VES: 'Dashboard Mínimo - PRUEBA' con check verde"
echo "   → El problema está en el contenido original del dashboard"
echo "   → Continuar con Paso 4 (restauración por bloques)"
echo ""
echo "   SI VES: Error de ParseError o pantalla de error"
echo "   → El problema está en layouts.modern o componentes globales"
echo "   → Revisar resources/views/layouts/modern.blade.php"
echo ""
echo "ARCHIVOS:"
echo "  - Vista mínima actual: $DASHBOARD"
echo "  - Backup original: $BACKUP"
echo ""
echo "Para restaurar el original:"
echo "  cp $BACKUP $DASHBOARD"
echo ""
echo "=========================================="
