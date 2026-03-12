#!/bin/bash
# Diagnóstico de dashboard.blade.php
# Ejecutar en el servidor: bash diagnose-dashboard.sh

echo "=== DIAGNÓSTICO DASHBOARD.BLADE.PHP ==="
echo ""

FILE="resources/views/dashboard.blade.php"

if [ ! -f "$FILE" ]; then
    echo "ERROR: Archivo no encontrado: $FILE"
    exit 1
fi

echo "1. INFORMACIÓN BÁSICA:"
echo "   Líneas: $(wc -l < $FILE)"
echo "   MD5: $(md5sum $FILE 2>/dev/null || md5 $FILE 2>/dev/null)"
echo ""

echo "2. CONTEO DE DIRECTIVAS:"
echo "   @if: $(grep -c '@if' $FILE)"
echo "   @endif: $(grep -c '@endif' $FILE)"
echo "   @foreach: $(grep -c '@foreach' $FILE)"
echo "   @endforeach: $(grep -c '@endforeach' $FILE)"
echo "   @forelse: $(grep -c '@forelse' $FILE)"
echo "   @endforelse: $(grep -c '@endforelse' $FILE)"
echo "   @section: $(grep -c '@section' $FILE)"
echo "   @endsection: $(grep -c '@endsection' $FILE)"
echo ""

echo "3. ÚLTIMAS 10 LÍNEAS:"
tail -10 $FILE
echo ""

echo "4. VERIFICACIÓN GIT:"
git status $FILE
echo ""
git diff $FILE | head -50
echo ""

echo "5. COMPILACIÓN BLADE:"
php artisan view:clear 2>&1
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
try {
    \$compiler = app('blade.compiler');
    \$content = file_get_contents('$FILE');
    \$compiled = \$compiler->compileString(\$content);
    echo 'COMPILACIÓN: OK';
} catch (Exception \$e) {
    echo 'COMPILACIÓN: ERROR - ' . \$e->getMessage();
}
echo \"\\n\";
"

echo ""
echo "=== FIN DIAGNÓSTICO ==="
