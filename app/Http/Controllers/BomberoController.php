<?php

namespace App\Http\Controllers;

use App\Exports\VolunteerImportTemplateExport;
use Illuminate\Http\Request;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\Specialty;
use App\Services\PlanService;
use App\Traits\TenantAdminAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Shuchkin\SimpleXLSX;

class BomberoController extends Controller
{
    use TenantAdminAuth;

    public function __construct(
        protected \App\Services\TenantPlanLimitService $limitService,
    ) {}

    public function apiIndex(Request $request)
    {
        // Return all firefighters for modals (refuerzo, reemplazo)
        $bomberos = Bombero::select('id', 'nombres', 'apellido_paterno', 'apellido_materno', 'guardia_id', 'es_refuerzo', 'es_titular')
            ->where(function ($q) {
                $q->whereNull('fuera_de_servicio')
                  ->orWhere('fuera_de_servicio', false);
            })
            ->orderBy('guardia_id')
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get();

        return response()->json($bomberos);
    }

    public function index(Request $request)
    {
        $this->requireTenantAdmin();

        $query = Bombero::query()->with(['guardia', 'specialties']);

        if ($request->filled('search')) {
            $search = trim((string) $request->get('search'));
            $query->where(function($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('correo', 'like', "%{$search}%")
                  ->orWhere('rut', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%")
                  ->orWhere('cargo_texto', 'like', "%{$search}%")
                  ->orWhere('numero_portatil', 'like', "%{$search}%");
            });
        }

        $volunteers = $query->orderBy('nombres')->paginate(20)->withQueryString();

        return view('admin.volunteers.index', compact('volunteers'));
    }

    public function create()
    {
        $this->requireTenantAdmin();

        $guardias = Guardia::all();
        $specialties = Specialty::query()->where('active', true)->orderBy('name')->get();
        $canCreateVolunteer = $this->limitService->canCreateVolunteer();
        $limitData = [
            'can_create' => $canCreateVolunteer,
            'message' => ! $canCreateVolunteer ? $this->limitService->getLimitExceededMessage('volunteers') : null,
        ];
        $volunteers_plan_usage = PlanService::usageLabel('volunteers');

        return view('admin.volunteers.create', compact('guardias', 'specialties', 'limitData', 'volunteers_plan_usage'));
    }

    public function store(Request $request)
    {
        $this->requireTenantAdmin();

        PlanService::assertCanIncrement('volunteers');

        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'rut' => 'nullable|string|unique:bomberos,rut',
            'numero_registro' => 'nullable|string|max:255',
            'correo' => 'nullable|email',
            'photo' => 'nullable|image|max:2048',
            'fecha_nacimiento' => 'nullable|date',
            'cargo_texto' => 'nullable|string|max:255',
            'numero_portatil' => 'nullable|string|max:255',
            'guardia_id' => 'nullable|exists:guardias,id',
            'fecha_ingreso' => 'nullable|date',
            'es_conductor' => 'nullable|boolean',
            'conductor_carros_bomba' => 'nullable|boolean',
            'es_operador_rescate' => 'nullable|boolean',
            'es_asistente_trauma' => 'nullable|boolean',
            'fuera_de_servicio' => 'nullable|boolean',
            'es_permanente' => 'nullable|boolean',
            'specialty_ids' => 'nullable|array',
            'specialty_ids.*' => 'integer|exists:specialties,id',
        ]);

        $data = $validated;
        $data['correo'] = $request->input('correo') ?: null;
        $data['es_conductor'] = $request->has('es_conductor');
        $data['conductor_carros_bomba'] = $data['es_conductor'] ? ($request->has('conductor_carros_bomba') ? true : false) : null;
        $data['es_operador_rescate'] = $request->has('es_operador_rescate');
        $data['es_asistente_trauma'] = $request->has('es_asistente_trauma');
        $data['fuera_de_servicio'] = $request->has('fuera_de_servicio');
        $data['es_permanente'] = $request->boolean('es_permanente');
        $data['numero_portatil'] = $request->input('numero_portatil') ?: null;
        $data['estado_asistencia'] = 'constituye';
        $data['es_titular'] = true;
        $data['es_jefe_guardia'] = false;
        $data['es_cambio'] = false;
        $data['es_sancion'] = false;

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('bomberos', 'public');
        }

        $bombero = Bombero::create($data);
        $bombero->specialties()->sync(
            Specialty::query()->whereIn('id', $request->input('specialty_ids', []))->where('active', true)->pluck('id')->all()
        );

        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario creado exitosamente.');
    }

    public function show(Request $request)
    {
        $this->requireTenantAdmin();
        
        $id = $request->route('volunteer');
        $volunteer = Bombero::with(['guardia', 'specialties'])->findOrFail((int) $id);
        
        return view('admin.volunteers.show', compact('volunteer'));
    }

    public function edit(Request $request)
    {
        $this->requireTenantAdmin();

        $id = $request->route('volunteer');
        $volunteer = Bombero::with('specialties')->findOrFail((int) $id);
        $guardias = Guardia::all();
        $specialties = Specialty::query()->where('active', true)->orderBy('name')->get();
        return view('admin.volunteers.edit', compact('volunteer', 'guardias', 'specialties'));
    }

    public function update(Request $request)
    {
        $this->requireTenantAdmin();

        $id = $request->route('volunteer');
        $volunteer = Bombero::findOrFail((int) $id);
        
        $request->validate([
            'nombres' => 'required|string|max:255',
            'rut' => 'nullable|string|unique:bomberos,rut,'.$id,
            'numero_registro' => 'nullable|string|max:255',
            'correo' => 'nullable|email',
            'photo' => 'nullable|image|max:2048',
            'fecha_nacimiento' => 'nullable|date',
            'cargo_texto' => 'nullable|string|max:255',
            'numero_portatil' => 'nullable|string|max:255',
            'fecha_ingreso' => 'nullable|date',
            'guardia_id' => 'nullable|exists:guardias,id',
            'fuera_de_servicio' => 'nullable|boolean',
            'es_permanente' => 'nullable|boolean',
            'specialty_ids' => 'nullable|array',
            'specialty_ids.*' => 'integer|exists:specialties,id',
        ]);

        $data = $request->only([
            'nombres',
            'apellido_paterno',
            'apellido_materno',
            'rut',
            'numero_registro',
            'correo',
            'fecha_nacimiento',
            'cargo_texto',
            'numero_portatil',
            'guardia_id',
            'fecha_ingreso',
            'fuera_de_servicio',
        ]);

        $data['es_conductor'] = $request->has('es_conductor');
        $data['conductor_carros_bomba'] = $data['es_conductor'] ? ($request->has('conductor_carros_bomba') ? true : false) : null;
        $data['es_operador_rescate'] = $request->has('es_operador_rescate');
        $data['es_asistente_trauma'] = $request->has('es_asistente_trauma');
        $data['fuera_de_servicio'] = $request->has('fuera_de_servicio');
        $data['es_permanente'] = $request->boolean('es_permanente');

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
        $volunteer->specialties()->sync(
            Specialty::query()->whereIn('id', $request->input('specialty_ids', []))->pluck('id')->all()
        );

        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario actualizado exitosamente.');
    }

    public function destroy(Request $request)
    {
        $this->requireTenantAdmin();

        $id = $request->route('volunteer');
        $volunteer = Bombero::findOrFail((int) $id);
        $volunteer->delete();
        return redirect()->route('admin.volunteers.index')->with('success', 'Voluntario eliminado exitosamente.');
    }

    public function destroyPhoto(Bombero $volunteer)
    {
        $this->requireTenantAdmin();

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

    public function purgeAll(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'confirm_text' => ['required', 'string', 'max:50'],
        ]);

        if (trim((string) $validated['confirm_text']) !== 'ELIMINAR TODO') {
            return redirect()->route('admin.volunteers.index')->with('warning', 'Confirmación inválida. Escribe ELIMINAR TODO para continuar.');
        }

        $photoPaths = Bombero::query()
            ->whereNotNull('photo_path')
            ->pluck('photo_path')
            ->filter()
            ->values();

        $count = Bombero::query()->count();

        Bombero::query()->delete();

        foreach ($photoPaths as $path) {
            Storage::disk('public')->delete((string) $path);
        }

        return redirect()->route('admin.volunteers.index')->with('success', "Se han eliminado $count voluntarios correctamente.");
    }

    public function importForm()
    {
        $specialties = Specialty::query()->where('active', true)->orderBy('name')->get();

        return view('admin.volunteers.import', compact('specialties'));
    }

    public function downloadImportTemplate()
    {
        $specialties = Specialty::query()->where('active', true)->orderBy('name')->get();

        return Excel::download(
            new VolunteerImportTemplateExport($specialties),
            'plantilla-importacion-voluntarios.xlsx'
        );
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
                $tempPath = storage_path('app/temp_import_' . uniqid() . '.xlsx');

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

        // Eliminar cabecera si existe
        $header = array_shift($data);

        // Guardar datos procesados en archivo temporal JSON para procesar por lotes
        $batchId = uniqid();
        $batchPath = storage_path('app/import_batch_' . $batchId . '.json');
        file_put_contents($batchPath, json_encode([
            'header' => $header,
            'rows' => $data,
        ]));

        return response()->json([
            'batchId' => $batchId,
            'totalRows' => count($data)
        ]);
    }

    public function processImport(Request $request)
    {
        $batchId = $request->input('batchId');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 50);

        $batchPath = storage_path('app/import_batch_' . $batchId . '.json');

        if (!file_exists($batchPath)) {
            return response()->json(['error' => 'Lote no encontrado o expirado.'], 404);
        }

        // Leer todo el archivo (no es ideal para archivos gigantescos, pero funcional para este contexto)
        // Optimización: Si fuera muy grande, usaríamos lectura por streams, pero json_decode carga todo a memoria igual.
        $payload = json_decode(file_get_contents($batchPath), true);
        $header = isset($payload['header']) && is_array($payload['header']) ? $payload['header'] : [];
        $rows = isset($payload['rows']) && is_array($payload['rows']) ? $payload['rows'] : [];

        $chunk = array_slice($rows, $offset, $limit);
        $processed = 0;
        $errors = [];
        $activeSpecialties = Specialty::query()->where('active', true)->get();
        $specialtiesByName = $activeSpecialties->keyBy(fn ($s) => mb_strtolower(trim($s->name)));
        $headerMap = $this->buildHeaderMap($header);
        $isNewTemplate = isset($headerMap['rut']) && isset($headerMap['nombres']);

        foreach ($chunk as $index => $row) {
            $rut = trim((string) ($this->col($row, $headerMap, 'rut', $isNewTemplate ? null : 3) ?? ''));

            if (!$rut) continue;

            $rutClean = strtoupper(preg_replace('/[^0-9K]/', '', $rut));
            $existsQuery = Bombero::query()->whereRaw("
                REPLACE(REPLACE(UPPER(rut), '.', ''), '-', '') = ?
            ", [$rutClean]);
            
            if ($existsQuery->exists()) continue;

            if (PlanService::exceedsLimit('volunteers', 1)) {
                $errors[] = $this->limitService->getLimitExceededMessage('volunteers');

                continue;
            }

            try {
                $val = function($idx) use ($row) {
                    return isset($row[$idx]) ? trim($row[$idx]) : null;
                };

                $nombres = trim((string) ($this->col($row, $headerMap, 'nombres', 0) ?? ''));
                $apellidoPaterno = $this->col($row, $headerMap, 'apellido_paterno', 1);
                $apellidoMaterno = $this->col($row, $headerMap, 'apellido_materno', 2);

                if (!$nombres) {
                    $errors[] = "Fila " . ($offset + $index + 2) . ": Falta 'nombres'";
                    continue;
                }

                $admissionDate = null;
                $rawDate = $this->col($row, $headerMap, 'fecha_ingreso', 8);
                if ($rawDate) {
                    try {
                        $admissionDate = \Carbon\Carbon::parse($rawDate)->toDateString();
                    } catch (\Exception $e) {}
                }

                $birthdate = null;
                $rawBirthdate = $this->col($row, $headerMap, 'fecha_cumpleanos', 6);
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

                $cargo = $this->col($row, $headerMap, 'cargo', 4);
                $portable = $this->col($row, $headerMap, 'telefono', 5) ?: $this->col($row, $headerMap, 'portatil', 5);
                $email = $this->col($row, $headerMap, 'email', 12);
                $numeroRegistro = $this->col($row, $headerMap, 'numero_registro', 13);
                $guardiaRaw = $this->col($row, $headerMap, 'guardia', 7);
                $specialtiesRaw = (string) ($this->col($row, $headerMap, 'especialidades', null) ?? '');

                $guardiaId = null;
                if ($guardiaRaw !== null && $guardiaRaw !== '') {
                    if (is_numeric($guardiaRaw)) {
                        $guardiaId = (int) $guardiaRaw;
                    } else {
                        $guardiaId = Guardia::query()
                            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim((string) $guardiaRaw))])
                            ->value('id');
                    }
                }

                $specialtyIds = [];
                if ($specialtiesRaw !== '') {
                    $requestedSpecialties = collect(explode(',', $specialtiesRaw))
                        ->map(fn ($item) => trim((string) $item))
                        ->filter()
                        ->values();

                    foreach ($requestedSpecialties as $requestedSpecialty) {
                        $specialty = $specialtiesByName->get(mb_strtolower($requestedSpecialty));

                        if (!$specialty) {
                            $errors[] = "Fila " . ($offset + $index + 2) . ": especialidad '{$requestedSpecialty}' no existe o está inactiva.";
                            continue 2;
                        }

                        $specialtyIds[] = $specialty->id;
                    }
                }

                $bombero = Bombero::create([
                    'nombres' => $nombres,
                    'apellido_paterno' => $apellidoPaterno,
                    'apellido_materno' => $apellidoMaterno,
                    'rut' => $rut,
                    'numero_registro' => $numeroRegistro ?: null,
                    'cargo_texto' => $cargo,
                    'numero_portatil' => $portable ?: null,
                    'fecha_nacimiento' => $birthdate,
                    'guardia_id' => $guardiaId,
                    'fecha_ingreso' => $admissionDate,
                    'correo' => $email ?: null,
                    'es_conductor' => $parseBool($this->col($row, $headerMap, 'conductor', 9)),
                    'es_operador_rescate' => $parseBool($this->col($row, $headerMap, 'operador_rescate', 10)),
                    'es_asistente_trauma' => $parseBool($this->col($row, $headerMap, 'asistente_trauma', 11)),
                    'estado_asistencia' => 'constituye',
                    'es_titular' => true,
                    'es_jefe_guardia' => false,
                    'es_cambio' => false,
                    'es_sancion' => false,
                ]);

                if (!empty($specialtyIds)) {
                    $bombero->specialties()->sync($specialtyIds);
                }
                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Fila " . ($offset + $index + 2) . ": " . $e->getMessage();
            }
        }

        // Si terminamos, borrar archivo temporal
        $finished = ($offset + $limit) >= count($rows);
        if ($finished) {
            unlink($batchPath);
        }

        return response()->json([
            'processed' => $processed,
            'errors' => $errors,
            'finished' => $finished
        ]);
    }

    private function buildHeaderMap(array $header): array
    {
        $aliases = [
            'rut' => 'rut',
            'nombres' => 'nombres',
            'apellido_paterno' => 'apellido_paterno',
            'apellido_materno' => 'apellido_materno',
            'telefono' => 'telefono',
            'email' => 'email',
            'guardia' => 'guardia',
            'especialidades' => 'especialidades',
            'fecha_ingreso' => 'fecha_ingreso',
            'fecha_cumpleanos' => 'fecha_cumpleanos',
            'cargo' => 'cargo',
            'portatil' => 'portatil',
            'conductor' => 'conductor',
            'operador_rescate' => 'operador_rescate',
            'asistente_trauma' => 'asistente_trauma',
            'numero_registro' => 'numero_registro',
        ];

        $map = [];
        foreach ($header as $index => $column) {
            $normalized = Str::of((string) $column)->lower()->ascii()->replace(' ', '_')->replace('-', '_')->value();
            if (isset($aliases[$normalized])) {
                $map[$aliases[$normalized]] = $index;
            }
        }

        return $map;
    }

    private function col(array $row, array $headerMap, string $name, ?int $fallback = null): mixed
    {
        if (array_key_exists($name, $headerMap) && array_key_exists($headerMap[$name], $row)) {
            return is_string($row[$headerMap[$name]]) ? trim($row[$headerMap[$name]]) : $row[$headerMap[$name]];
        }

        if ($fallback !== null && array_key_exists($fallback, $row)) {
            return is_string($row[$fallback]) ? trim($row[$fallback]) : $row[$fallback];
        }

        return null;
    }

    public function import(Request $request)
    {
        // ... (Mantener método original como fallback o eliminar si se desea reemplazar totalmente)
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
            $tempPath = storage_path('app/temp_import_' . uniqid() . '.xlsx');

            $uploaded = $request->file('file');
            $uploaded->move(dirname($tempPath), basename($tempPath));

            $xlsx = SimpleXLSX::parse($tempPath);
            if (!$xlsx) {
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                return back()->withErrors(['file' => 'Error leyendo Excel.']);
            }

            $data = $xlsx->rows();

            if (file_exists($tempPath)) {
                unlink($tempPath);
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
                $rutClean = strtoupper(preg_replace('/[^0-9K]/', '', $rut));
                $existsQuery->orWhereRaw("
                    REPLACE(REPLACE(UPPER(rut), '.', ''), '-', '') = ?
                ", [$rutClean]);
            }
            
            if ($existsQuery->exists()) {
                // Opcional: Actualizar existente? Por ahora saltamos
                continue;
            }

            if (PlanService::exceedsLimit('volunteers', 1)) {
                $errors[] = $this->limitService->getLimitExceededMessage('volunteers');

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
