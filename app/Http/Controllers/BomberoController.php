<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bombero;
use App\Models\Guardia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BomberoController extends Controller
{
    public function index(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }

        $query = Bombero::query()->with('guardia');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%")
                  ->orWhere('rut', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%");
            });
        }

        $volunteers = $query->orderBy('nombres')->paginate(20);

        return view('admin.volunteers.index', compact('volunteers'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }
        $guardias = Guardia::all();
        return view('admin.volunteers.create', compact('guardias'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'rut' => 'nullable|string|unique:bomberos,rut',
            'correo' => 'nullable|email',
            'photo' => 'nullable|image|max:2048',
            'fecha_nacimiento' => 'nullable|date',
            'cargo_texto' => 'nullable|string|max:255',
            'numero_portatil' => 'nullable|string|max:255',
            'guardia_id' => 'nullable|exists:guardias,id',
            'fecha_ingreso' => 'nullable|date',
            'es_conductor' => 'nullable|boolean',
            'es_operador_rescate' => 'nullable|boolean',
            'es_asistente_trauma' => 'nullable|boolean',
            'fuera_de_servicio' => 'nullable|boolean',
        ]);

        $data = $validated;
        $data['correo'] = $request->input('correo') ?: null;
        $data['es_conductor'] = $request->has('es_conductor');
        $data['es_operador_rescate'] = $request->has('es_operador_rescate');
        $data['es_asistente_trauma'] = $request->has('es_asistente_trauma');
        $data['fuera_de_servicio'] = $request->has('fuera_de_servicio');
        $data['numero_portatil'] = $request->input('numero_portatil') ?: null;
        $data['estado_asistencia'] = 'constituye';
        $data['es_titular'] = true;
        $data['es_jefe_guardia'] = false;
        $data['es_cambio'] = false;
        $data['es_sancion'] = false;

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('bomberos', 'public');
        }

        Bombero::create($data);

        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario creado exitosamente.');
    }

    public function edit($id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }
        $volunteer = Bombero::findOrFail($id);
        $guardias = Guardia::all();
        return view('admin.volunteers.edit', compact('volunteer', 'guardias'));
    }

    public function update(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }
        $volunteer = Bombero::findOrFail($id);
        
        $request->validate([
            'nombres' => 'required|string|max:255',
            'rut' => 'nullable|string|unique:bomberos,rut,'.$id,
            'correo' => 'nullable|email',
            'photo' => 'nullable|image|max:2048',
            'fecha_nacimiento' => 'nullable|date',
            'cargo_texto' => 'nullable|string|max:255',
            'numero_portatil' => 'nullable|string|max:255',
            'fecha_ingreso' => 'nullable|date',
            'guardia_id' => 'nullable|exists:guardias,id',
            'fuera_de_servicio' => 'nullable|boolean',
        ]);

        $data = $request->only([
            'nombres',
            'apellido_paterno',
            'apellido_materno',
            'rut',
            'correo',
            'fecha_nacimiento',
            'cargo_texto',
            'numero_portatil',
            'guardia_id',
            'fecha_ingreso',
            'fuera_de_servicio',
        ]);

        $data['es_conductor'] = $request->has('es_conductor');
        $data['es_operador_rescate'] = $request->has('es_operador_rescate');
        $data['es_asistente_trauma'] = $request->has('es_asistente_trauma');
        $data['fuera_de_servicio'] = $request->has('fuera_de_servicio');

        $data['numero_portatil'] = $request->input('numero_portatil') ?: null;

        if (empty($data['correo'])) {
            $data['correo'] = null;
        }

        if ($request->hasFile('photo')) {
            $newPath = $request->file('photo')->store('bomberos', 'public');
            if ($volunteer->photo_path) {
                Storage::disk('public')->delete($volunteer->photo_path);
            }
            $data['photo_path'] = $newPath;
        }

        $volunteer->update($data);

        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario actualizado exitosamente.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }
        $volunteer = Bombero::findOrFail($id);
        $volunteer->delete();
        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario eliminado exitosamente.');
    }

    public function destroyPhoto(Bombero $volunteer)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }

        if ($volunteer->photo_path) {
            Storage::disk('public')->delete($volunteer->photo_path);
        }

        $volunteer->update([
            'photo_path' => null,
        ]);

        return redirect()->back()->with('success', 'Foto eliminada correctamente.');
    }

    public function bulkDestroy(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:bomberos,id',
        ]);

        $ids = $request->input('ids');
        $count = count($ids);

        Bombero::whereIn('id', $ids)->delete();

        return redirect()->route('admin.volunteers.index')->with('success', "Se han eliminado $count voluntarios correctamente.");
    }

    public function importForm()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }
        return view('admin.volunteers.import');
    }

    public function uploadImport(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
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
        if (auth()->user()->role !== 'super_admin') {
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

            $existsQuery = Bombero::query()->where('rut', $rut);
            
            if ($existsQuery->exists()) continue;

            try {
                $val = function($idx) use ($row) {
                    return isset($row[$idx]) ? trim($row[$idx]) : null;
                };

                $nombres = $val(0);
                $apellidoPaterno = $val(1);
                $apellidoMaterno = $val(2);

                if (!$nombres) {
                    $errors[] = "Fila " . ($offset + $index + 2) . ": Falta 'nombres'";
                    continue;
                }

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

                Bombero::create([
                    'nombres' => $nombres,
                    'apellido_paterno' => $apellidoPaterno,
                    'apellido_materno' => $apellidoMaterno,
                    'rut' => $val(3),
                    'cargo_texto' => $cargo,
                    'numero_portatil' => $portable ?: null,
                    'fecha_nacimiento' => $birthdate,
                    'guardia_id' => $val(7) ?: null,
                    'fecha_ingreso' => $admissionDate,
                    'correo' => $email ?: null,
                    'es_conductor' => $parseBool($val(9)),
                    'es_operador_rescate' => $parseBool($val(10)),
                    'es_asistente_trauma' => $parseBool($val(11)),
                    'estado_asistencia' => 'constituye',
                    'es_titular' => true,
                    'es_jefe_guardia' => false,
                    'es_cambio' => false,
                    'es_sancion' => false,
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

            $existsQuery = Bombero::query();
            if ($email) {
                $existsQuery->where('correo', $email);
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

                $nombres = $val(0);
                if (!$nombres) {
                    $errors[] = "Fila " . ($index + 2) . ": Falta 'nombres'";
                    continue;
                }

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

                Bombero::create([
                    'nombres' => $nombres,
                    'apellido_paterno' => $val(1),
                    'apellido_materno' => $val(2),
                    'rut' => $val(3),
                    'numero_registro' => $val(4),
                    'numero_portatil' => $val(5),
                    'cargo_texto' => $val(6),
                    'correo' => $email ?: null,
                    'direccion_calle' => $val(8),
                    'direccion_numero' => $val(9),
                    'fecha_ingreso' => $admissionDate,

                    'estado_asistencia' => 'constituye',
                    'es_titular' => true,
                    'es_jefe_guardia' => false,
                    'es_cambio' => false,
                    'es_sancion' => false,
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
