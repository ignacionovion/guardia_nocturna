<?php
/**
 * Script de aislamiento de bloques Blade
 * Divide dashboard.blade.php en secciones y prueba cada una
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$originalFile = 'resources/views/dashboard.blade.php';
$backupFile = 'resources/views/dashboard.blade.php.backup';
$testFile = 'resources/views/dashboard.blade.php.test';

// Lee el archivo original
$content = file_get_contents($originalFile);
$lines = explode("\n", $content);
$totalLines = count($lines);

echo "=== AISLAMIENTO DE BLOQUES ===\n";
echo "Total líneas: $totalLines\n\n";

// Estructura base
$baseStart = <<<'BASE'
@extends('layouts.modern')

@section('title', 'Dashboard - ' . branding()->nombre_empresa)
@section('page-title', 'Dashboard')

@section('content')
    @if(Auth::check() && Auth::user()->role === 'guardia' && isset($myGuardia) && $myGuardia)
BASE;

$baseEnd = <<<'BASE'
    @else
        <div class="p-4"><h1>Otra rama</h1></div>
    @endif
@endsection
BASE;

// Función para probar un bloque
function testBlock($name, $block, $baseStart, $baseEnd) {
    global $testFile;
    
    $testContent = $baseStart . "\n" . $block . "\n" . $baseEnd;
    file_put_contents($testFile, $testContent);
    
    try {
        $compiler = app('blade.compiler');
        $compiled = $compiler->compileString($testContent);
        echo "✓ $name: OK\n";
        return true;
    } catch (Exception $e) {
        echo "✗ $name: ERROR - " . substr($e->getMessage(), 0, 100) . "\n";
        return false;
    }
}

// Probar bloques grandes
$blocks = [
    'php-inicial' => implode("\n", array_slice($lines, 7, 61)), // @php inicial
    'header-ui' => implode("\n", array_slice($lines, 68, 107)), // Header UI
    'staff-forelse' => implode("\n", array_slice($lines, 175, 239)), // Staff forelse
    'out-of-service' => implode("\n", array_slice($lines, 414, 30)), // Out of service
    'stats-cards' => implode("\n", array_slice($lines, 444, 50)), // Stats cards
    'replacements' => implode("\n", array_slice($lines, 494, 100)), // Replacements
    'modales' => implode("\n", array_slice($lines, 594, 400)), // Modales y scripts
    'scripts-finales' => implode("\n", array_slice($lines, 994, 500)), // Scripts finales
];

echo "Probando bloques grandes:\n";
$failedBlocks = [];
foreach ($blocks as $name => $block) {
    if (!testBlock($name, $block, $baseStart, $baseEnd)) {
        $failedBlocks[] = $name;
    }
}

echo "\n";
if (empty($failedBlocks)) {
    echo "Todos los bloques individuales compilan OK.\n";
    echo "El problema puede ser interacción entre bloques o estructura anidada.\n";
} else {
    echo "Bloques con errores: " . implode(', ', $failedBlocks) . "\n";
}

// Limpiar
unlink($testFile);

echo "\n=== FIN AISLAMIENTO ===\n";
