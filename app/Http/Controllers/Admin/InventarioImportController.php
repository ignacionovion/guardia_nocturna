<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryWarehouse;
use Illuminate\Http\Request;

class InventarioImportController extends Controller
{
    public function importForm(Request $request)
    {
        $bodega = InventoryWarehouse::query()
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$bodega) {
            return redirect()->route('inventario.config.form');
        }

        return view('admin.inventario.import', [
            'bodega' => $bodega,
        ]);
    }

    public function uploadImport(Request $request)
    {
        $bodega = InventoryWarehouse::query()
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$bodega) {
            return response()->json(['error' => 'No hay bodega configurada.'], 400);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = strtolower((string) $file->getClientOriginalExtension());

        $data = [];

        try {
            if (in_array($extension, ['csv', 'txt'], true)) {
                $data = array_map('str_getcsv', file($path));
            } elseif ($extension === 'xlsx') {
                $tempPath = storage_path('app/temp_import_inventario_' . uniqid() . '.xlsx');
                $uploaded = $request->file('file');
                $uploaded->move(dirname($tempPath), basename($tempPath));

                $xlsx = \Shuchkin\SimpleXLSX::parse($tempPath);
                if (!$xlsx) {
                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                    return response()->json(['error' => 'Error leyendo Excel.'], 400);
                }

                $data = $xlsx->rows();

                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error procesando archivo: ' . $e->getMessage()], 500);
        }

        if (empty($data)) {
            return response()->json(['error' => 'No se encontraron datos en el archivo.'], 400);
        }

        $normalized = $this->normalizarFilas($data);
        if (empty($normalized)) {
            return response()->json(['error' => 'No se encontraron ítems importables en el archivo.'], 400);
        }

        $batchId = uniqid('inv_', true);
        $batchPath = storage_path('app/import_inventario_batch_' . $batchId . '.json');
        file_put_contents($batchPath, json_encode($normalized));

        return response()->json([
            'batchId' => $batchId,
            'totalRows' => count($normalized),
        ]);
    }

    public function processImport(Request $request)
    {
        $bodega = InventoryWarehouse::query()
            ->where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$bodega) {
            return response()->json(['error' => 'No hay bodega configurada.'], 400);
        }

        $batchId = (string) $request->input('batchId');
        $offset = (int) $request->input('offset', 0);
        $limit = (int) $request->input('limit', 50);

        $batchPath = storage_path('app/import_inventario_batch_' . $batchId . '.json');
        if (!file_exists($batchPath)) {
            return response()->json(['error' => 'Lote no encontrado o expirado.'], 404);
        }

        $data = json_decode(file_get_contents($batchPath), true);
        $chunk = array_slice($data, $offset, $limit);

        $procesados = 0;
        $creados = 0;
        $actualizados = 0;
        $errores = [];

        foreach ($chunk as $index => $row) {
            if (!is_array($row) || count($row) < 2) {
                continue;
            }

            $val = function (int $idx) use ($row) {
                return isset($row[$idx]) ? trim((string) $row[$idx]) : null;
            };

            $categoria = $val(0);
            $titulo = $val(1);
            $variante = $val(2);
            $unidad = $val(3);
            $stockRaw = $val(4);

            if (!$titulo) {
                continue;
            }

            $stock = 0;
            if ($stockRaw !== null && $stockRaw !== '') {
                $stock = (int) preg_replace('/[^0-9\-]/', '', (string) $stockRaw);
                if ($stock < 0) {
                    $stock = 0;
                }
            }

            try {
                $query = InventoryItem::query()
                    ->where('bodega_id', $bodega->id)
                    ->where('titulo', $titulo);

                if ($categoria !== null && $categoria !== '') {
                    $query->where('categoria', $categoria);
                } else {
                    $query->whereNull('categoria');
                }

                if ($variante !== null && $variante !== '') {
                    $query->where('variante', $variante);
                } else {
                    $query->whereNull('variante');
                }

                $item = $query->first();

                if ($item) {
                    $item->update([
                        'unidad' => $unidad ?: $item->unidad,
                        'stock' => $stock,
                        'activo' => true,
                    ]);
                    $actualizados++;
                } else {
                    InventoryItem::create([
                        'bodega_id' => $bodega->id,
                        'categoria' => $categoria ?: null,
                        'titulo' => $titulo,
                        'variante' => $variante ?: null,
                        'unidad' => $unidad ?: null,
                        'stock' => $stock,
                        'activo' => true,
                    ]);
                    $creados++;
                }

                $procesados++;
            } catch (\Exception $e) {
                $errores[] = 'Fila ' . ($offset + $index + 2) . ': ' . $e->getMessage();
            }
        }

        $finished = ($offset + $limit) >= count($data);
        if ($finished) {
            unlink($batchPath);
        }

        return response()->json([
            'procesados' => $procesados,
            'creados' => $creados,
            'actualizados' => $actualizados,
            'errores' => $errores,
            'finished' => $finished,
        ]);
    }

    private function normalizarFilas(array $rows): array
    {
        $out = [];

        $currentCategoria = null;
        $subCategoriaIzq = null;
        $subCategoriaDer = null;
        $grupoTituloIzq = null;
        $grupoTituloDer = null;

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $cells = [];
            foreach ($row as $k => $v) {
                $cells[$k] = is_string($v) ? trim($v) : $v;
            }

            $nonEmpty = array_values(array_filter($cells, function ($v) {
                return !($v === null || $v === '');
            }));

            // Saltar filas completamente vacías
            if (count($nonEmpty) === 0) {
                continue;
            }

            $c0 = $cells[0] ?? null;
            $c1 = $cells[1] ?? null;
            $c2 = $cells[2] ?? null;
            $c4 = $cells[4] ?? null;
            $c5 = $cells[5] ?? null;
            $c6 = $cells[6] ?? null;
            $c8 = $cells[8] ?? null;

            // Detectar categoría principal: una sola celda con texto (ej: TRAUMA)
            if (count($nonEmpty) === 1 && is_string($c0) && $c0 !== '') {
                $currentCategoria = $c0;
                $subCategoriaIzq = null;
                $subCategoriaDer = null;
                $grupoTituloIzq = null;
                $grupoTituloDer = null;
                continue;
            }

            // Detectar encabezados (subcategorías / tablas)
            // Ej: ["FERULAS", "", "ESTADO", "", "CARGADORES", "", "ESTADO", "", "SOLICITAR"]
            if (is_string($c2) && mb_strtoupper($c2) === 'ESTADO' && is_string($c0) && $c0 !== '') {
                $subCategoriaIzq = $c0;
                $grupoTituloIzq = null;
            }
            if (is_string($c6) && mb_strtoupper($c6) === 'ESTADO' && is_string($c4) && $c4 !== '') {
                $subCategoriaDer = $c4;
                $grupoTituloDer = null;
            }

            // Detectar grupo tipo: "COLLARES CERVICALES" + ESTADO y luego variantes (ADULTOS, etc)
            if (is_string($c0) && $c0 !== '' && (is_string($c2) && mb_strtoupper($c2) === 'ESTADO') && ($c1 === null || $c1 === '')) {
                $grupoTituloIzq = $c0;
                continue;
            }
            if (is_string($c4) && $c4 !== '' && (is_string($c6) && mb_strtoupper($c6) === 'ESTADO') && ($c5 === null || $c5 === '')) {
                $grupoTituloDer = $c4;
                continue;
            }

            $categoriaBase = $currentCategoria ?: null;

            $categoriaIzq = $categoriaBase;
            if ($subCategoriaIzq) {
                $categoriaIzq = trim(($categoriaIzq ? ($categoriaIzq . ' / ') : '') . $subCategoriaIzq);
            }

            $categoriaDer = $categoriaBase;
            if ($subCategoriaDer) {
                $categoriaDer = trim(($categoriaDer ? ($categoriaDer . ' / ') : '') . $subCategoriaDer);
            }

            // Parse lado izquierdo (col 0..2)
            $izqCantidad = null;
            $izqTitulo = null;
            $izqVariante = null;
            if (isset($cells[0]) && is_numeric($cells[0])) {
                $izqCantidad = (int) $cells[0];
            }

            if (is_string($c1) && $c1 !== '') {
                // normal: [cantidad, titulo, estado]
                if ($grupoTituloIzq) {
                    $izqTitulo = $grupoTituloIzq;
                    $izqVariante = $c1;
                } else {
                    $izqTitulo = $c1;
                }
            } elseif (is_string($c0) && $c0 !== '' && !is_numeric($c0)) {
                // casos donde el título viene en col 0
                if ($grupoTituloIzq) {
                    $izqTitulo = $grupoTituloIzq;
                    $izqVariante = $c0;
                } else {
                    $izqTitulo = $c0;
                }
            }

            if ($izqTitulo) {
                $out[] = [
                    (string) ($categoriaIzq ?? ''),
                    (string) $izqTitulo,
                    $izqVariante ? (string) $izqVariante : null,
                    null,
                    $izqCantidad ?? 0,
                ];
            }

            // Parse lado derecho (col 4..8)
            $derCantidad = null;
            $derTitulo = null;
            if (isset($cells[4]) && is_numeric($cells[4])) {
                $derCantidad = (int) $cells[4];
            }

            if (is_string($c5) && $c5 !== '') {
                $derTitulo = $c5;
            }

            // También hay casos donde solo hay una solicitud en col 8 sin item derecho
            if ($derTitulo) {
                $out[] = [
                    (string) ($categoriaDer ?? ''),
                    (string) $derTitulo,
                    null,
                    null,
                    $derCantidad ?? 0,
                ];
            }
        }

        // Filtrar filas sin título
        $out = array_values(array_filter($out, function ($r) {
            return isset($r[1]) && trim((string) $r[1]) !== '';
        }));

        // Quitar duplicados exactos
        $seen = [];
        $unique = [];
        foreach ($out as $r) {
            $key = json_encode([$r[0], $r[1], $r[2], $r[3]]);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $r;
        }

        return $unique;
    }
}
