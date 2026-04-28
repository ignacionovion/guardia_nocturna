<?php

namespace App\Http\Controllers;

use App\Exports\VolunteerImportTemplateExport;
use Illuminate\Http\Request;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\Specialty;
use App\Models\SystemSetting;
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
        $showSpecialtiesSetupAlert = SystemSetting::getValue('specialties_customized', '0') !== '1';

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

        return view('admin.volunteers.index', compact('volunteers', 'showSpecialtiesSetupAlert'));
    }

    public function create()
    {
        $this->requireTenantAdmin();
        $showSpecialtiesSetupAlert = SystemSetting::getValue('specialties_customized', '0') !== '1';

        $guardias = Guardia::all();
        $cargos = Bombero::CARGOS;
        $specialties = Specialty::query()->where('active', true)->orderBy('name')->get();
        $canCreateVolunteer = $this->limitService->canCreateVolunteer();
        $limitData = [
            'can_create' => $canCreateVolunteer,
            'message' => ! $canCreateVolunteer ? $this->limitService->getLimitExceededMessage('volunteers') : null,
        ];
        $volunteers_plan_usage = PlanService::usageLabel('volunteers');

        return view('admin.volunteers.create', compact('guardias', 'cargos', 'specialties', 'limitData', 'volunteers_plan_usage', 'showSpecialtiesSetupAlert'));
    }

    public function store(Request $request)
    {
        $this->requireTenantAdmin();

        PlanService::assertCanIncrement('volunteers');
        $request->merge([
            'cargo_texto' => $this->normalizeCargo($request->input('cargo_texto')),
        ]);

        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'rut' => 'required|string|unique:bomberos,rut',
            'numero_registro' => 'nullable|string|max:255',
            'correo' => 'nullable|email',
            'photo' => 'nullable|image|max:2048',
            'fecha_nacimiento' => 'nullable|date',
            'cargo_texto' => 'required|string|in:' . implode(',', Bombero::CARGOS),
            'numero_portatil' => 'nullable|string|max:255',
            'guardia_id' => 'nullable|exists:guardias,id',
            'fecha_ingreso' => 'nullable|date',
            'fuera_de_servicio' => 'nullable|boolean',
            'es_permanente' => 'nullable|boolean',
            'specialty_ids' => 'nullable|array',
            'specialty_ids.*' => 'integer|exists:specialties,id',
        ]);

        $data = $validated;
        $data['correo'] = $request->input('correo') ?: null;
        $data['cargo_texto'] = $this->normalizeCargo($request->input('cargo_texto'));
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
        $showSpecialtiesSetupAlert = SystemSetting::getValue('specialties_customized', '0') !== '1';

        $id = $request->route('volunteer');
        $volunteer = Bombero::with('specialties')->findOrFail((int) $id);
        $guardias = Guardia::all();
        $cargos = Bombero::CARGOS;
        $specialties = Specialty::query()->where('active', true)->orderBy('name')->get();
        return view('admin.volunteers.edit', compact('volunteer', 'guardias', 'cargos', 'specialties', 'showSpecialtiesSetupAlert'));
    }

    public function update(Request $request)
    {
        $this->requireTenantAdmin();

        $id = $request->route('volunteer');
        $volunteer = Bombero::findOrFail((int) $id);
        $request->merge([
            'cargo_texto' => $this->normalizeCargo($request->input('cargo_texto')),
        ]);
        
        $request->validate([
            'nombres' => 'required|string|max:255',
            'apellido_paterno' => 'required|string|max:255',
            'apellido_materno' => 'required|string|max:255',
            'rut' => 'required|string|unique:bomberos,rut,'.$id,
            'numero_registro' => 'nullable|string|max:255',
            'correo' => 'nullable|email',
            'photo' => 'nullable|image|max:2048',
            'fecha_nacimiento' => 'nullable|date',
            'cargo_texto' => 'required|string|in:' . implode(',', Bombero::CARGOS),
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

        $data['cargo_texto'] = $this->normalizeCargo($request->input('cargo_texto'));
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
        $guardias = Guardia::query()->orderBy('name')->get(['id', 'name']);

        return Excel::download(
            new VolunteerImportTemplateExport($specialties, $guardias),
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
                    return response()->json(['error' => 'No se pudo procesar el archivo. Asegúrate de usar la plantilla oficial y que los datos estén correctamente formateados.'], 400);
                }

                $data = $xlsx->rows();

                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'No se pudo procesar el archivo. Asegúrate de usar la plantilla oficial y que los datos estén correctamente formateados.'], 500);
        }

        if (empty($data)) {
            return response()->json(['error' => 'No se pudo procesar el archivo. Asegúrate de usar la plantilla oficial y que los datos estén correctamente formateados.'], 400);
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
        try {
            $batchId = $request->input('batchId');
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 50);

            $batchPath = storage_path('app/import_batch_' . $batchId . '.json');

            if (!file_exists($batchPath)) {
                return response()->json(['error' => 'No se pudo procesar el archivo. Asegúrate de usar la plantilla oficial y que los datos estén correctamente formateados.'], 404);
            }

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
            $line = $offset + $index + 2;

            $hasAnyValue = collect($row)->contains(fn ($value) => trim((string) $value) !== '');
            if (!$hasAnyValue) {
                continue;
            }

            $rut = trim((string) ($this->col($row, $headerMap, 'rut', $isNewTemplate ? null : 3) ?? ''));

            if (!$rut) {
                $errors[] = "Fila {$line}: campo 'rut' es obligatorio.";
                continue;
            }

            if (!$this->isValidRutFormat($rut)) {
                $errors[] = "Fila {$line}: el campo 'rut' no tiene un formato válido.";
                continue;
            }

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
                $apellidoPaterno = trim((string) ($this->col($row, $headerMap, 'apellido_paterno', 1) ?? ''));
                $apellidoMaterno = trim((string) ($this->col($row, $headerMap, 'apellido_materno', 2) ?? ''));

                if (!$nombres) {
                    $errors[] = "Fila {$line}: campo 'nombres' es obligatorio.";
                    continue;
                }

                if (!$apellidoPaterno) {
                    $errors[] = "Fila {$line}: campo 'apellido_paterno' es obligatorio.";
                    continue;
                }

                if (!$apellidoMaterno) {
                    $errors[] = "Fila {$line}: campo 'apellido_materno' es obligatorio.";
                    continue;
                }

                $birthdate = $this->parseImportDate($this->col($row, $headerMap, 'fecha_nacimiento', 6));

                $parseBool = function ($value) {
                    $v = trim((string) $value);
                    if ($v === '') return false;
                    $v = mb_strtolower($v);
                    return in_array($v, ['1', 'si', 'sí', 'true', 'x', 'yes'], true);
                };

                $cargoRaw = trim((string) ($this->col($row, $headerMap, 'cargo', $isNewTemplate ? 4 : null) ?? ''));
                if ($cargoRaw === '' && !$isNewTemplate && !array_key_exists('cargo', $headerMap)) {
                    $cargoRaw = 'bombero';
                }
                if ($cargoRaw === '') {
                    $errors[] = "Fila {$line}: campo 'cargo' es obligatorio.";
                    continue;
                }
                $cargo = $this->normalizeCargo($cargoRaw);
                if (!in_array($cargo, Bombero::CARGOS, true)) {
                    $errors[] = "Fila {$line}: cargo inválido (valor recibido: {$cargoRaw}).";
                    continue;
                }

                $portable = $this->col($row, $headerMap, 'numero_radial')
                    ?: $this->col($row, $headerMap, 'telefono', 5)
                    ?: $this->col($row, $headerMap, 'portatil', 5);
                $email = $this->col($row, $headerMap, 'email', 12);
                $numeroRegistro = $this->col($row, $headerMap, 'numero_registro', 13);
                $guardiaRaw = $this->col($row, $headerMap, 'guardia', 7);
                $specialtiesRaw = (string) ($this->col($row, $headerMap, 'especialidades', null) ?? '');
                $direccionRaw = trim((string) ($this->col($row, $headerMap, 'direccion') ?? ''));
                $estadoAsistencia = trim((string) ($this->col($row, $headerMap, 'estado') ?? ''));
                $allowedStates = ['constituye', 'reemplazo', 'permiso', 'ausente', 'falta', 'licencia'];

                if ($estadoAsistencia !== '' && !in_array(mb_strtolower($estadoAsistencia), $allowedStates, true)) {
                    $errors[] = "Fila {$line}: estado '{$estadoAsistencia}' inválido. Valores permitidos: " . implode(', ', $allowedStates) . '.';
                    continue;
                }

                $guardiaId = null;
                if ($guardiaRaw !== null && $guardiaRaw !== '') {
                    if (is_numeric($guardiaRaw)) {
                        $candidateGuardiaId = (int) $guardiaRaw;
                        $guardiaId = Guardia::query()->whereKey($candidateGuardiaId)->value('id');
                    } else {
                        $guardiaId = Guardia::query()
                            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim((string) $guardiaRaw))])
                            ->value('id');
                    }

                    if (!$guardiaId) {
                        $errors[] = "Fila {$line}: guardia '{$guardiaRaw}' no existe.";
                        continue;
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
                            $errors[] = "Fila {$line}: especialidad '{$requestedSpecialty}' no existe o está inactiva.";
                            continue 2;
                        }

                        $specialtyIds[] = $specialty->id;
                    }
                }

                $legacyFlagToSpecialty = [
                    'conductor' => 'Conductor',
                    'operador_rescate' => 'Operador Rescate',
                    'asistente_trauma' => 'Asistente Trauma',
                ];

                foreach ($legacyFlagToSpecialty as $legacyColumn => $specialtyName) {
                    $legacyValue = $this->col($row, $headerMap, $legacyColumn, null);
                    if (!$parseBool($legacyValue)) {
                        continue;
                    }

                    $legacySpecialty = $specialtiesByName->get(mb_strtolower($specialtyName));
                    if (!$legacySpecialty) {
                        $errors[] = "Fila {$line}: especialidad '{$specialtyName}' requerida por columna legacy '{$legacyColumn}' no existe o está inactiva.";
                        continue 2;
                    }

                    $specialtyIds[] = $legacySpecialty->id;
                }

                $addressStreet = null;
                $addressNumber = null;
                if ($direccionRaw !== '') {
                    if (preg_match('/^(.*?)[,\s]+(\d+[A-Za-z\-]*)$/u', $direccionRaw, $matches)) {
                        $addressStreet = trim((string) $matches[1]) ?: $direccionRaw;
                        $addressNumber = trim((string) $matches[2]) ?: null;
                    } else {
                        $addressStreet = $direccionRaw;
                    }
                }

                $bombero = Bombero::create([
                    'nombres' => $this->normalizeImportUpper($nombres),
                    'apellido_paterno' => $this->normalizeImportUpper($apellidoPaterno),
                    'apellido_materno' => $this->normalizeImportUpper($apellidoMaterno),
                    'rut' => $rut,
                    'numero_registro' => $numeroRegistro ?: null,
                    'cargo_texto' => $this->normalizeImportUpper($cargo),
                    'numero_portatil' => $portable ?: null,
                    'fecha_nacimiento' => $birthdate,
                    'direccion_calle' => $this->normalizeImportUpper($addressStreet),
                    'direccion_numero' => $addressNumber,
                    'guardia_id' => $guardiaId,
                    'fecha_ingreso' => null,
                    'correo' => $email ?: null,
                    'estado_asistencia' => $estadoAsistencia !== '' ? mb_strtolower($estadoAsistencia) : 'constituye',
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
                $errors[] = "Fila {$line}: El formato de uno o más datos en el archivo no es válido. Revisa especialmente fechas, números o campos obligatorios.";
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
        } catch (\Throwable) {
            return response()->json([
                'error' => 'No se pudo procesar el archivo. Asegúrate de usar la plantilla oficial y que los datos estén correctamente formateados.'
            ], 500);
        }
    }

    private function buildHeaderMap(array $header): array
    {
        $aliases = [
            'rut' => 'rut',
            'nombres' => 'nombres',
            'apellido_paterno' => 'apellido_paterno',
            'apellido_materno' => 'apellido_materno',
            'telefono' => 'telefono',
            'telefono_contacto' => 'telefono',
            'email' => 'email',
            'correo' => 'email',
            'e_mail' => 'email',
            'guardia' => 'guardia',
            'especialidades' => 'especialidades',
            'fecha_ingreso' => 'fecha_ingreso',
            'fecha_cumpleanos' => 'fecha_nacimiento',
            'fecha_nacimiento' => 'fecha_nacimiento',
            'cargo' => 'cargo',
            'cargo_texto' => 'cargo',
            'portatil' => 'portatil',
            'numero_portatil' => 'portatil',
            'numero_radial' => 'numero_radial',
            'conductor' => 'conductor',
            'es_conductor' => 'conductor',
            'is_driver' => 'conductor',
            'operador_rescate' => 'operador_rescate',
            'es_operador_rescate' => 'operador_rescate',
            'is_rescue_operator' => 'operador_rescate',
            'asistente_trauma' => 'asistente_trauma',
            'es_asistente_trauma' => 'asistente_trauma',
            'is_trauma_assistant' => 'asistente_trauma',
            'numero_registro' => 'numero_registro',
            'direccion' => 'direccion',
            'direccion_calle' => 'direccion',
            'estado' => 'estado',
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

    private function parseImportDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return \Carbon\Carbon::createFromDate(1899, 12, 30)
                    ->addDays((int) $value)
                    ->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return \Carbon\Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeCargo(?string $cargo): string
    {
        return mb_strtolower(trim((string) $cargo));
    }

    private function normalizeImportUpper(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return mb_strtoupper($value, 'UTF-8');
    }

    private function isValidRutFormat(string $rut): bool
    {
        return (bool) preg_match('/^[0-9]{1,2}\.?[0-9]{3}\.?[0-9]{3}-?[0-9kK]$/', trim($rut));
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
