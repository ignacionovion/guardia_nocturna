<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmergencyKey;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Shuchkin\SimpleXLSX;

class EmergencyKeyController extends Controller
{
    public function index(Request $request)
    {
        $query = EmergencyKey::query()->orderBy('code');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $keys = $query->paginate(30);

        return view('admin.emergency_keys.index', compact('keys'));
    }

    public function show(string $id)
    {
        return redirect()->route('admin.emergency-keys.edit', $id);
    }

    public function create()
    {
        return view('admin.emergency_keys.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:emergency_keys,code'],
            'description' => ['required', 'string'],
        ]);

        EmergencyKey::create($validated);

        return redirect()->route('admin.emergency-keys.index')->with('success', 'Clave creada correctamente.');
    }

    public function edit(string $id)
    {
        $key = EmergencyKey::findOrFail($id);
        return view('admin.emergency_keys.edit', compact('key'));
    }

    public function update(Request $request, string $id)
    {
        $key = EmergencyKey::findOrFail($id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('emergency_keys', 'code')->ignore($key->id)],
            'description' => ['required', 'string'],
        ]);

        $key->update($validated);

        return redirect()->route('admin.emergency-keys.index')->with('success', 'Clave actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $key = EmergencyKey::findOrFail($id);
        $key->delete();

        return redirect()->route('admin.emergency-keys.index')->with('success', 'Clave eliminada correctamente.');
    }

    public function importForm()
    {
        return view('admin.emergency_keys.import');
    }

    public function uploadImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = $file->getClientOriginalExtension();

        $data = [];

        try {
            if (in_array(strtolower($extension), ['csv', 'txt'])) {
                $data = array_map('str_getcsv', file($path));
            } elseif (strtolower($extension) === 'xlsx') {
                $tempPath = storage_path('app/temp_import_keys_' . uniqid() . '.xlsx');

                $uploaded = $request->file('file');
                $uploaded->move(dirname($tempPath), basename($tempPath));

                $xlsx = SimpleXLSX::parse($tempPath);
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

        array_shift($data);

        $batchId = uniqid();
        $batchPath = storage_path('app/import_keys_batch_' . $batchId . '.json');
        file_put_contents($batchPath, json_encode($data));

        return response()->json([
            'batchId' => $batchId,
            'totalRows' => count($data),
        ]);
    }

    public function processImport(Request $request)
    {
        $batchId = $request->input('batchId');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 50);

        $batchPath = storage_path('app/import_keys_batch_' . $batchId . '.json');
        if (!file_exists($batchPath)) {
            return response()->json(['error' => 'Lote no encontrado o expirado.'], 404);
        }

        $data = json_decode(file_get_contents($batchPath), true);
        $chunk = array_slice($data, $offset, $limit);
        $processed = 0;
        $errors = [];

        foreach ($chunk as $index => $row) {
            if (count($row) < 1) {
                continue;
            }

            $val = function ($idx) use ($row) {
                return isset($row[$idx]) ? trim($row[$idx]) : null;
            };

            $code = $val(0);
            $description = $val(1);

            if (!$code) {
                continue;
            }

            if (EmergencyKey::where('code', $code)->exists()) {
                continue;
            }

            try {
                EmergencyKey::create([
                    'code' => $code,
                    'description' => $description ?: '',
                ]);
                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Fila " . ($offset + $index + 2) . ": " . $e->getMessage();
            }
        }

        $finished = ($offset + $limit) >= count($data);
        if ($finished) {
            unlink($batchPath);
        }

        return response()->json([
            'processed' => $processed,
            'errors' => $errors,
            'finished' => $finished,
        ]);
    }
}
