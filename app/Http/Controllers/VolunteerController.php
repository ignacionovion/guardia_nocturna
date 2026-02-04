<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Firefighter;
use App\Models\Guardia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VolunteerController extends Controller
{
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }

        $query = Firefighter::query()->with('guardia');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('rut', 'like', "%{$search}%")
                  ->orWhere('last_name_paternal', 'like', "%{$search}%");
            });
        }

        $volunteers = $query->orderBy('name')->paginate(20);

        return view('admin.volunteers.index', compact('volunteers'));
    }

    public function create()
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }
        $guardias = Guardia::all();
        return view('admin.volunteers.create', compact('guardias'));
    }

    public function store(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name_paternal' => 'nullable|string|max:255',
            'last_name_maternal' => 'nullable|string|max:255',
            'rut' => 'nullable|string|unique:firefighters,rut',
            'email' => 'nullable|email',
            'birthdate' => 'nullable|date',
            'position_text' => 'nullable|string|max:255',
            'portable_number' => 'nullable|string|max:255',
            'guardia_id' => 'nullable|exists:guardias,id',
            'admission_date' => 'nullable|date',
            'is_driver' => 'nullable|boolean',
            'is_rescue_operator' => 'nullable|boolean',
            'is_trauma_assistant' => 'nullable|boolean',
        ]);

        $data = $validated;
        $data['email'] = $request->input('email') ?: null;
        $data['is_driver'] = $request->has('is_driver');
        $data['is_rescue_operator'] = $request->has('is_rescue_operator');
        $data['is_trauma_assistant'] = $request->has('is_trauma_assistant');
        $data['portable_number'] = $request->input('portable_number') ?: null;
        $data['attendance_status'] = 'constituye';
        $data['is_titular'] = true;
        $data['is_shift_leader'] = false;
        $data['is_exchange'] = false;
        $data['is_penalty'] = false;

        Firefighter::create($data);

        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario creado exitosamente.');
    }

    public function edit($id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }
        $volunteer = Firefighter::findOrFail($id);
        $guardias = Guardia::all();
        return view('admin.volunteers.edit', compact('volunteer', 'guardias'));
    }

    public function update(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }
        $volunteer = Firefighter::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'rut' => 'nullable|string|unique:firefighters,rut,'.$id,
            'email' => 'nullable|email',
            'birthdate' => 'nullable|date',
            'position_text' => 'nullable|string|max:255',
            'portable_number' => 'nullable|string|max:255',
            'admission_date' => 'nullable|date',
            'guardia_id' => 'nullable|exists:guardias,id',
        ]);

        $data = $request->only([
            'name',
            'last_name_paternal',
            'last_name_maternal',
            'rut',
            'email',
            'birthdate',
            'position_text',
            'portable_number',
            'guardia_id',
            'admission_date',
        ]);

        $data['is_driver'] = $request->has('is_driver');
        $data['is_rescue_operator'] = $request->has('is_rescue_operator');
        $data['is_trauma_assistant'] = $request->has('is_trauma_assistant');

        $data['portable_number'] = $request->input('portable_number') ?: null;

        if (empty($data['email'])) {
            $data['email'] = null;
        }

        $volunteer->update($data);

        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario actualizado exitosamente.');
    }

    public function destroy($id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }
        $volunteer = Firefighter::findOrFail($id);
        $volunteer->delete();
        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario eliminado exitosamente.');
    }

    public function bulkDestroy(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:firefighters,id',
        ]);

        $ids = $request->input('ids');
        $count = count($ids);

        Firefighter::whereIn('id', $ids)->delete();

        return redirect()->route('admin.volunteers.index')->with('success', "Se han eliminado $count voluntarios correctamente.");
    }

    public function importForm()
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }
        return view('admin.volunteers.import');
    }

    public function uploadImport(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

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
                $tempPath = storage_path('app/temp_import_' . uniqid() . '.xlsx');
                move_uploaded_file($path, $tempPath);
                
                $scriptPath = base_path('app/Scripts/excel_to_json.py');
                $command = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($tempPath);
                $output = shell_exec($command);
                
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                
                $jsonData = json_decode($output, true);
                
                if (isset($jsonData['error'])) {
                    return response()->json(['error' => 'Error leyendo Excel: ' . $jsonData['error']], 400);
                }
                
                if (is_array($jsonData)) {
                    $data = $jsonData;
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error procesando archivo: ' . $e->getMessage()], 500);
        }

        if (empty($data)) {
            return response()->json(['error' => 'No se encontraron datos en el archivo.'], 400);
        }

        // Eliminar cabecera si existe
        $header = array_shift($data);

        // Guardar datos procesados en archivo temporal JSON para procesar por lotes
        $batchId = uniqid();
        $batchPath = storage_path('app/import_batch_' . $batchId . '.json');
        file_put_contents($batchPath, json_encode($data));

        return response()->json([
            'batchId' => $batchId,
            'totalRows' => count($data)
        ]);
    }

    public function processImport(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $batchId = $request->input('batchId');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 50);

        $batchPath = storage_path('app/import_batch_' . $batchId . '.json');

        if (!file_exists($batchPath)) {
            return response()->json(['error' => 'Lote no encontrado o expirado.'], 404);
        }

        // Leer todo el archivo (no es ideal para archivos gigantescos, pero funcional para este contexto)
        // Optimización: Si fuera muy grande, usaríamos lectura por streams, pero json_decode carga todo a memoria igual.
        $data = json_decode(file_get_contents($batchPath), true);
        
        $chunk = array_slice($data, $offset, $limit);
        $processed = 0;
        $errors = [];

        foreach ($chunk as $index => $row) {
            // Validar longitud mínima
            if (count($row) < 4) continue;

            $rut = isset($row[3]) ? trim($row[3]) : null;

            if (!$rut) continue;

            $existsQuery = Firefighter::query()->where('rut', $rut);
            
            if ($existsQuery->exists()) continue;

            try {
                $val = function($idx) use ($row) {
                    return isset($row[$idx]) ? trim($row[$idx]) : null;
                };

                $admissionDate = null;
                $rawDate = $val(8);
                if ($rawDate) {
                    try {
                        $admissionDate = \Carbon\Carbon::parse($rawDate)->toDateString();
                    } catch (\Exception $e) {}
                }

                $birthdate = null;
                $rawBirthdate = $val(6);
                if ($rawBirthdate) {
                    try {
                        $birthdate = \Carbon\Carbon::parse($rawBirthdate)->toDateString();
                    } catch (\Exception $e) {}
                }

                $parseBool = function ($value) {
                    $v = trim((string) $value);
                    if ($v === '') return false;
                    $v = mb_strtolower($v);
                    return in_array($v, ['1', 'si', 'sí', 'true', 'x', 'yes'], true);
                };

                $cargo = $val(4);
                $portable = $val(5);
                $email = $val(12);

                Firefighter::create([
                    'name' => $val(0),
                    'last_name_paternal' => $val(1),
                    'last_name_maternal' => $val(2),
                    'rut' => $val(3),
                    'position_text' => $cargo,
                    'portable_number' => $portable ?: null,
                    'birthdate' => $birthdate,
                    'guardia_id' => $val(7) ?: null,
                    'admission_date' => $admissionDate,
                    'email' => $email ?: null,
                    'is_driver' => $parseBool($val(9)),
                    'is_rescue_operator' => $parseBool($val(10)),
                    'is_trauma_assistant' => $parseBool($val(11)),
                    'attendance_status' => 'constituye',
                    'is_titular' => true,
                    'is_shift_leader' => false,
                    'is_exchange' => false,
                    'is_penalty' => false,
                ]);
                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Fila " . ($offset + $index + 2) . ": " . $e->getMessage();
            }
        }

        // Si terminamos, borrar archivo temporal
        $finished = ($offset + $limit) >= count($data);
        if ($finished) {
            unlink($batchPath);
        }

        return response()->json([
            'processed' => $processed,
            'errors' => $errors,
            'finished' => $finished
        ]);
    }

    public function import(Request $request)
    {
        // ... (Mantener método original como fallback o eliminar si se desea reemplazar totalmente)
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $extension = $file->getClientOriginalExtension();
        
        $data = [];

        if (in_array(strtolower($extension), ['csv', 'txt'])) {
            $data = array_map('str_getcsv', file($path));
        } elseif (strtolower($extension) === 'xlsx') {
            // Guardar temporalmente con extensión correcta para que openpyxl no falle
            $tempPath = storage_path('app/temp_import_' . uniqid() . '.xlsx');
            move_uploaded_file($path, $tempPath);
            
            // Usar script python para leer xlsx
            $scriptPath = base_path('app/Scripts/excel_to_json.py');
            $command = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($tempPath);
            $output = shell_exec($command);
            
            // Eliminar archivo temporal
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            $jsonData = json_decode($output, true);
            
            if (isset($jsonData['error'])) {
                return back()->withErrors(['file' => 'Error leyendo Excel: ' . $jsonData['error']]);
            }
            
            if (is_array($jsonData)) {
                $data = $jsonData;
            }
        }

        if (empty($data)) {
            return back()->withErrors(['file' => 'No se pudieron leer datos del archivo.']);
        }

        $header = array_shift($data); // Asumimos que la primera fila es cabecera

        // Nuevo Mapeo basado en imagen:
        // A (0): NOMBRES
        // B (1): APELLIDO PATERNO
        // C (2): APELLIDO MATERNO
        // D (3): RUT
        // E (4): REGISTRO
        // F (5): PORTATIL
        // G (6): CARGO
        // H (7): E-MAIL
        // I (8): DIRECCION CALLE
        // J (9): DIRECCION NUMERO
        // K (10): FECHA INGRESO

        $count = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            // Validar longitud mínima (al menos hasta email)
            if (count($row) < 8) continue; 

            // Verificar si el usuario ya existe por email o RUT
            $email = isset($row[7]) ? trim($row[7]) : null;
            $rut = isset($row[3]) ? trim($row[3]) : null;

            if (!$email && !$rut) continue;
            
            // Limpieza básica
            if ($email) $email = strtolower($email);

            $existsQuery = Firefighter::query();
            if ($email) {
                $existsQuery->where('email', $email);
            }
            if ($rut) {
                $existsQuery->orWhere('rut', $rut);
            }
            
            if ($existsQuery->exists()) {
                // Opcional: Actualizar existente? Por ahora saltamos
                continue;
            }

            try {
                // Helper para obtener valor seguro
                $val = function($idx) use ($row) {
                    return isset($row[$idx]) ? trim($row[$idx]) : null;
                };

                // Parsear fecha de ingreso si existe
                $admissionDate = null;
                $rawDate = $val(10);
                if ($rawDate) {
                    try {
                        // Intentar parsear fecha Excel (puede venir como 'YYYY-MM-DD HH:MM:SS' o string)
                        $admissionDate = \Carbon\Carbon::parse($rawDate)->toDateString();
                    } catch (\Exception $e) {
                        // Fecha inválida, dejar null
                    }
                }

                Firefighter::create([
                    'name' => $val(0),
                    'last_name_paternal' => $val(1),
                    'last_name_maternal' => $val(2),
                    'rut' => $val(3),
                    'registration_number' => $val(4),
                    'portable_number' => $val(5),
                    'position_text' => $val(6),
                    'email' => $email ?: null,
                    'address_street' => $val(8),
                    'address_number' => $val(9),
                    'admission_date' => $admissionDate,

                    'attendance_status' => 'constituye',
                    'is_titular' => true,
                    'is_shift_leader' => false,
                    'is_exchange' => false,
                    'is_penalty' => false,
                ]);
                $count++;
            } catch (\Exception $e) {
                $errors[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
                continue;
            }
        }

        if (count($errors) > 0) {
            return redirect()->route('admin.volunteers.index')
                ->with('success', "Se importaron $count voluntarios.")
                ->with('warning', 'Hubo errores en algunas filas: ' . implode('; ', array_slice($errors, 0, 5)));
        }

        return redirect()->route('admin.volunteers.index')->with('success', "Se importaron $count voluntarios correctamente.");
    }
}
