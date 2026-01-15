<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Guardia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VolunteerController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        // QUERY BASE: Solo usuarios con rol 'bombero' o 'jefe_guardia'
        // Se usa closure para agrupar las condiciones OR y evitar problemas con otros wheres
        $query = User::where(function($q) {
            $q->where('role', 'bombero')
              ->orWhere('role', 'jefe_guardia');
        });

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
            'name' => 'required|string|max:255',
            'last_name_paternal' => 'nullable|string|max:255',
            'last_name_maternal' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'rut' => 'nullable|string|unique:users,rut',
            'guardia_id' => 'nullable|exists:guardias,id',
            'role' => 'required|in:bombero,jefe_guardia,capitania,super_admin',
            'registration_number' => 'nullable|string',
            'company_registration_number' => 'nullable|string',
            'portable_number' => 'nullable|string',
            'position_text' => 'nullable|string',
            'admission_date' => 'nullable|date',
            'birthdate' => 'nullable|date',
            'profession' => 'nullable|string',
            'phone' => 'nullable|string',
            'address_commune' => 'nullable|string',
            'address_street' => 'nullable|string',
            'address_number' => 'nullable|string',
            'company' => 'nullable|string',
            'call_code' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make(Str::random(12));
        
        // Manejo explícito de booleanos
        $data['is_driver'] = $request->has('is_driver');
        $data['is_rescue_operator'] = $request->has('is_rescue_operator');
        $data['is_trauma_assistant'] = $request->has('is_trauma_assistant');
        $data['is_shift_leader'] = $request->has('is_shift_leader');
        
        User::create($data);

        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario creado exitosamente.');
    }

    public function edit($id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }
        $volunteer = User::findOrFail($id);
        $guardias = Guardia::all();
        return view('admin.volunteers.edit', compact('volunteer', 'guardias'));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }
        $volunteer = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'rut' => 'nullable|string|unique:users,rut,'.$id,
            'admission_date' => 'nullable|date',
            'birthdate' => 'nullable|date',
            'registration_number' => 'nullable|string',
            'portable_number' => 'nullable|string',
        ]);

        $data = $request->except(['password']);
        
        // Manejo explícito de booleanos para asegurar que se guarden los 'false'
        $data['is_driver'] = $request->has('is_driver');
        $data['is_rescue_operator'] = $request->has('is_rescue_operator');
        $data['is_trauma_assistant'] = $request->has('is_trauma_assistant');
        $data['is_shift_leader'] = $request->has('is_shift_leader');
        
        $volunteer->update($data);

        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario actualizado exitosamente.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }
        $volunteer = User::findOrFail($id);
        $volunteer->delete();
        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario eliminado exitosamente.');
    }

    public function bulkDestroy(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        $ids = $request->input('ids');
        $count = count($ids);

        // Evitar eliminar al propio usuario logueado si se selecciona a sí mismo
        if (in_array(auth()->id(), $ids)) {
            return back()->with('warning', 'No puedes eliminar tu propia cuenta en una acción masiva.');
        }

        User::whereIn('id', $ids)->delete();

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
            if (count($row) < 8) continue; 

            $email = isset($row[7]) ? trim($row[7]) : null;
            $rut = isset($row[3]) ? trim($row[3]) : null;

            if (!$email && !$rut) continue;
            if ($email) $email = strtolower($email);

            $existsQuery = User::query();
            if ($email) $existsQuery->where('email', $email);
            if ($rut) $existsQuery->orWhere('rut', $rut);
            
            if ($existsQuery->exists()) continue;

            try {
                $val = function($idx) use ($row) {
                    return isset($row[$idx]) ? trim($row[$idx]) : null;
                };

                $admissionDate = null;
                $rawDate = $val(10);
                if ($rawDate) {
                    try {
                        $admissionDate = \Carbon\Carbon::parse($rawDate)->toDateString();
                    } catch (\Exception $e) {}
                }

                User::create([
                    'name' => $val(0),
                    'last_name_paternal' => $val(1),
                    'last_name_maternal' => $val(2),
                    'rut' => $val(3),
                    'registration_number' => $val(4),
                    'portable_number' => $val(5),
                    'position_text' => $val(6),
                    'email' => $email ?? 'no-email-' . uniqid() . '@system.local',
                    'address_street' => $val(8),
                    'address_number' => $val(9),
                    'admission_date' => $admissionDate,
                    'password' => Hash::make('password'),
                    'role' => 'bombero',
                    'age' => 0,
                    'years_of_service' => 0, 
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

            $existsQuery = User::query();
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

                User::create([
                    'name' => $val(0),
                    'last_name_paternal' => $val(1),
                    'last_name_maternal' => $val(2),
                    'rut' => $val(3),
                    'registration_number' => $val(4),
                    'portable_number' => $val(5),
                    'position_text' => $val(6),
                    'email' => $email ?? 'no-email-' . uniqid() . '@system.local', // Fallback si no hay email pero si RUT
                    'address_street' => $val(8),
                    'address_number' => $val(9),
                    'admission_date' => $admissionDate,
                    
                    // Campos por defecto
                    'password' => Hash::make('password'), // Se podría usar el RUT como pass inicial
                    'role' => 'bombero',
                    'age' => 0,
                    'years_of_service' => 0, 
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
