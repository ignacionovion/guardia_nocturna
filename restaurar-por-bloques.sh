#!/bin/bash
# =============================================================================
# SCRIPT DE RESTAURACIÓN POR BLOQUES - DASHBOARD.BLADE.PHP
# =============================================================================
# Ejecutar SOLO si la vista mínima funciona correctamente
# Uso: bash restaurar-por-bloques.sh
# =============================================================================

set -e

DASHBOARD="resources/views/dashboard.blade.php"
BACKUP_PATTERN="resources/views/dashboard.blade.php.backup.*"

echo "=========================================="
echo "RESTAURACIÓN POR BLOQUES - DASHBOARD"
echo "=========================================="
echo ""

# Encontrar el backup más reciente
BACKUP=$(ls -t $BACKUP_PATTERN 2>/dev/null | head -1)

if [ -z "$BACKUP" ]; then
    echo "ERROR: No se encontró backup del dashboard original"
    echo "Buscando: $BACKUP_PATTERN"
    exit 1
fi

echo "Backup encontrado: $BACKUP"
echo "Líneas en backup: $(wc -l < "$BACKUP")"
echo ""

# Función para probar un bloque
test_block() {
    local name="$1"
    local start_line="$2"
    local end_line="$3"
    
    echo "--- Probando bloque: $name (líneas $start_line-$end_line) ---"
    
    # Crear archivo con estructura base + bloque
    head -6 "$BACKUP" > "$DASHBOARD"  # Cabecera @extends, @section
    sed -n "${start_line},${end_line}p" "$BACKUP" >> "$DASHBOARD"
    echo "@endsection" >> "$DASHBOARD"
    
    # Limpiar y probar
    php artisan view:clear 2>/dev/null || true
    rm -rf storage/framework/views/*.php 2>/dev/null || true
    
    # Compilar
    if php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
try {
    \$compiler = app('blade.compiler');
    \$content = file_get_contents('$DASHBOARD');
    \$compiled = \$compiler->compileString(\$content);
    exit(0);
} catch (Exception \$e) {
    echo \$e->getMessage();
    exit(1);
}
" 2>&1; then
        echo "✓ BLOQUE $name: COMPILA OK"
        return 0
    else
        echo "✗ BLOQUE $name: ERROR DE COMPILACIÓN"
        return 1
    fi
}

echo "ESTRATEGIA DE BISECCIÓN:"
echo "========================"
echo ""

TOTAL_LINES=$(wc -l < "$BACKUP")
echo "Total líneas en backup: $TOTAL_LINES"
echo ""

# Definir bloques principales
# Línea 7 = inicio @if principal guardia
# Línea 626 = @else (rama admin)
# Línea 1063 = @endif principal

echo "BLOQUES A PROBAR:"
echo "  A. Rama Guardia completa (líneas 7-625)"
echo "  B. Rama Admin completa (líneas 626-1062)"  
echo "  C. Modales y scripts (líneas 1063-2988)"
echo ""

read -p "¿Probar bloques principales? (s/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Ss]$ ]]; then
    FAILED_BLOCKS=""
    
    # Bloque A: Rama Guardia
    if ! test_block "RAMA_GUARDIA" 7 625; then
        FAILED_BLOCKS="$FAILED_BLOCKS RAMA_GUARDIA"
    fi
    
    # Bloque B: Rama Admin  
    if ! test_block "RAMA_ADMIN" 626 1062; then
        FAILED_BLOCKS="$FAILED_BLOCKS RAMA_ADMIN"
    fi
    
    # Bloque C: Post-endif (modales/scripts)
    if ! test_block "MODALES_SCRIPTS" 1063 $TOTAL_LINES; then
        FAILED_BLOCKS="$FAILED_BLOCKS MODALES_SCRIPTS"
    fi
    
    echo ""
    echo "=========================================="
    echo "RESULTADO DE BLOQUES PRINCIPALES"
    echo "=========================================="
    
    if [ -z "$FAILED_BLOCKS" ]; then
        echo "✓ Todos los bloques principales compilan OK"
        echo ""
        echo "INTERPRETACIÓN:"
        echo "  El problema es la INTERACCIÓN entre bloques,"
        echo "  no un bloque individual."
        echo ""
        echo "SIGUIENTE PASO:"
        echo "  Probar combinaciones de bloques para"
        echo "  identificar qué combinación rompe."
    else
        echo "✗ Bloques con error:$FAILED_BLOCKS"
        echo ""
        echo "SIGUIENTE PASO:"
        for block in $FAILED_BLOCKS; do
            echo "  - Dividir $block en sub-bloques más pequeños"
        done
    fi
fi

echo ""
echo "=========================================="
echo "RESTAURACIÓN MANUAL"
echo "=========================================="
echo "Para restaurar el archivo original:"
echo "  cp $BACKUP $DASHBOARD"
echo ""
echo "Para restaurar y limpiar caché:"
echo "  cp $BACKUP $DASHBOARD && php artisan view:clear"
echo ""
