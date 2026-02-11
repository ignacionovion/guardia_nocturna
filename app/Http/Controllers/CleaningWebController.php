<?php

namespace App\Http\Controllers;

use App\Models\CleaningAssignment;
use App\Models\CleaningTask;
use App\Models\Guardia;
use App\Models\Bombero;
use App\Models\MapaBomberoUsuarioLegacy;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CleaningWebController extends Controller
{
    private function resolveGuardiaIdForGuardiaUser(User $user): ?int
    {
        if ($user->guardia_id) {
            return (int) $user->guardia_id;
        }

        $byName = Guardia::whereRaw('lower(name) = ?', [strtolower($user->name)])->value('id');
        if ($byName) {
            return (int) $byName;
        }

        $emailLocal = explode('@', (string) $user->email)[0] ?? '';
        $emailLocal = str_replace('.', ' ', $emailLocal);
        $byEmailLocal = Guardia::whereRaw('lower(name) = ?', [strtolower($emailLocal)])->value('id');
        if ($byEmailLocal) {
            return (int) $byEmailLocal;
        }

        return null;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'guardia') {
            abort(403, 'No autorizado.');
        }

        $guardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
        if (!$guardiaId) {
            abort(403, 'Cuenta de guardia sin guardia asignada.');
        }

        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))->startOfDay()
            : now()->startOfDay();

        $desiredTasks = [
            ['name' => 'Aseo Pieza N°1', 'description' => null],
            ['name' => 'Aseo Pieza N°2', 'description' => null],
            ['name' => 'Aseo Pieza N°3', 'description' => null],
            ['name' => 'Aseo Pieza N°4', 'description' => null],
            ['name' => 'Aseo Pieza N°5', 'description' => null],
            ['name' => 'Aseo Sector Duchas', 'description' => null],
            ['name' => 'Aseo Sector Baños', 'description' => null],
            ['name' => 'Aseo Sala de Estar', 'description' => null],
            ['name' => 'Aseo Cocina Y Quincho', 'description' => null],
        ];

        foreach ($desiredTasks as $task) {
            CleaningTask::firstOrCreate(['name' => $task['name']], ['description' => $task['description']]);
        }

        $tasks = CleaningTask::whereIn('name', array_map(fn ($t) => $t['name'], $desiredTasks))
            ->orderByRaw('FIELD(name, ' . implode(',', array_fill(0, count($desiredTasks), '?')) . ')', array_map(fn ($t) => $t['name'], $desiredTasks))
            ->get();

        $users = Bombero::where('guardia_id', $guardiaId)
            ->where('estado_asistencia', 'constituye')
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get();

        $assignments = CleaningAssignment::with(['cleaningTask', 'firefighter', 'user'])
            ->whereDate('assigned_date', $date->toDateString())
            ->whereIn('cleaning_task_id', $tasks->pluck('id'))
            ->get();

        $assignmentsByTaskId = $assignments->keyBy('cleaning_task_id');

        return view('aseo', compact('date', 'tasks', 'users', 'assignmentsByTaskId'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'guardia') {
            abort(403, 'No autorizado.');
        }

        $guardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
        if (!$guardiaId) {
            abort(403, 'Cuenta de guardia sin guardia asignada.');
        }

        $validated = $request->validate([
            'assigned_date' => 'required|date',
            'assignments' => 'required|array',
            'assignments.*' => 'nullable|exists:bomberos,id',
        ]);

        $date = Carbon::parse($validated['assigned_date'])->startOfDay();

        $firefighterIds = collect($validated['assignments'])->filter()->map(fn ($v) => (int) $v)->unique()->values();
        if ($firefighterIds->isNotEmpty()) {
            $validCount = Bombero::whereIn('id', $firefighterIds)
                ->where('guardia_id', $guardiaId)
                ->count();

            if ($validCount !== $firefighterIds->count()) {
                abort(403, 'No puedes asignar aseo a bomberos de otra guardia.');
            }
        }

        $taskIds = collect(array_keys($validated['assignments']))->map(fn ($v) => (int) $v)->values();

        CleaningAssignment::whereIn('cleaning_task_id', $taskIds)
            ->whereDate('assigned_date', $date->toDateString())
            ->delete();

        foreach ($validated['assignments'] as $taskId => $assignedUserId) {
            if (!$assignedUserId) {
                continue;
            }

            $data = [
                'cleaning_task_id' => (int) $taskId,
                'firefighter_id' => (int) $assignedUserId,
                'assigned_date' => $date->toDateString(),
                'status' => 'pending',
            ];

            if (Schema::hasColumn('cleaning_assignments', 'user_id')) {
                $legacyUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', (int) $assignedUserId)->value('user_id');
                if ($legacyUserId) {
                    $data['user_id'] = (int) $legacyUserId;
                }
            }

            CleaningAssignment::create($data);
        }

        return redirect()->route('guardia.aseo', ['date' => $date->toDateString()])->with('success', 'Asignación de aseo guardada correctamente.');
    }
}
