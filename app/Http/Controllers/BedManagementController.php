<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Bombero;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BedManagementController extends Controller
{
    private function scheduleTimezone(): string
    {
        return SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
    }

    /**
     * Get all beds with current assignments for a guardia
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $guardiaId = $user->guardia_id;

        if (!$guardiaId) {
            return response()->json(['error' => 'Usuario sin guardia asignada'], 403);
        }

        // Get all beds
        $beds = Bed::query()
            ->orderBy('number')
            ->get()
            ->map(function (Bed $bed) {
                $assignment = BedAssignment::query()
                    ->where('bed_id', $bed->id)
                    ->whereNull('released_at')
                    ->with('firefighter')
                    ->first();

                return [
                    'id' => $bed->id,
                    'number' => $bed->number,
                    'status' => $bed->status,
                    'assignment' => $assignment ? [
                        'id' => $assignment->id,
                        'firefighter_id' => $assignment->firefighter_id,
                        'firefighter_name' => $assignment->firefighter?->nombre_completo ?? 'Desconocido',
                        'assigned_at' => $assignment->assigned_at?->toISOString(),
                        'assigned_source' => $assignment->assigned_source ?? 'manual',
                    ] : null,
                ];
            });

        return response()->json([
            'beds' => $beds,
        ]);
    }

    /**
     * Get available firefighters for bed assignment
     */
    public function availableFirefighters(Request $request)
    {
        $user = auth()->user();
        $guardiaId = $user->guardia_id;

        if (!$guardiaId) {
            return response()->json(['error' => 'Usuario sin guardia asignada'], 403);
        }

        // Get active replacements to exclude replaced firefighters
        $activeReplacements = \App\Models\ReemplazoBombero::where('estado', 'activo')
            ->whereHas('originalFirefighter', fn ($q) => $q->where('guardia_id', $guardiaId))
            ->get();

        $replacedIds = $activeReplacements->pluck('bombero_titular_id')->toArray();

        // Get all active firefighters from this guardia (excluding out-of-service and replaced)
        $firefighters = Bombero::query()
            ->where('guardia_id', $guardiaId)
            ->whereNotIn('id', $replacedIds)
            ->where(function ($q) {
                $q->where('fuera_de_servicio', false)
                  ->orWhereNull('fuera_de_servicio');
            })
            ->orderBy('nombre_completo')
            ->get()
            ->map(function (Bombero $b) {
                // Check if already has a bed
                $currentBed = BedAssignment::query()
                    ->where('firefighter_id', $b->id)
                    ->whereNull('released_at')
                    ->with('bed')
                    ->first();

                return [
                    'id' => $b->id,
                    'nombre_completo' => $b->nombre_completo,
                    'cargo' => $b->cargo,
                    'current_bed' => $currentBed ? $currentBed->bed?->number : null,
                ];
            });

        return response()->json([
            'firefighters' => $firefighters,
        ]);
    }

    /**
     * Assign a bed to a firefighter manually
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'bed_id' => 'required|exists:beds,id',
            'firefighter_id' => 'required|exists:bomberos,id',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);
        $firefighter = Bombero::findOrFail($validated['firefighter_id']);

        // Verify firefighter belongs to same guardia as user
        if ($firefighter->guardia_id !== auth()->user()->guardia_id) {
            return response()->json(['error' => 'Bombero no pertenece a tu guardia'], 403);
        }

        // Release current bed assignment if exists
        $currentAssignment = BedAssignment::query()
            ->where('bed_id', $bed->id)
            ->whereNull('released_at')
            ->first();

        if ($currentAssignment) {
            $currentAssignment->update(['released_at' => now()]);
            $bed->update(['status' => 'available']);
        }

        // Release firefighter's previous bed if exists
        $firefighterPreviousBed = BedAssignment::query()
            ->where('firefighter_id', $firefighter->id)
            ->whereNull('released_at')
            ->with('bed')
            ->first();

        if ($firefighterPreviousBed) {
            $firefighterPreviousBed->update(['released_at' => now()]);
            if ($firefighterPreviousBed->bed) {
                $firefighterPreviousBed->bed->update(['status' => 'available']);
            }
        }

        // Create new assignment
        $assignment = BedAssignment::create([
            'bed_id' => $bed->id,
            'firefighter_id' => $firefighter->id,
            'assigned_at' => now(),
            'notes' => 'Asignado manualmente desde panel live',
            'assigned_source' => 'manual',
            'assigned_ip' => (string) ($request->ip() ?? ''),
            'assigned_user_agent' => (string) $request->userAgent(),
        ]);

        // Mark bed as occupied
        $bed->update(['status' => 'occupied']);

        // Send notification
        \App\Services\NotificationService::bedAssigned(
            auth()->user(),
            $firefighter,
            $bed->number ?? $bed->id,
            $firefighter->guardia,
            'Manual'
        );

        return response()->json([
            'success' => true,
            'message' => 'Cama asignada correctamente',
            'assignment' => [
                'id' => $assignment->id,
                'bed_number' => $bed->number,
                'firefighter_name' => $firefighter->nombre_completo,
            ],
        ]);
    }

    /**
     * Release a bed assignment
     */
    public function release(Request $request)
    {
        $validated = $request->validate([
            'bed_id' => 'required|exists:beds,id',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);

        $assignment = BedAssignment::query()
            ->where('bed_id', $bed->id)
            ->whereNull('released_at')
            ->first();

        if (!$assignment) {
            return response()->json(['error' => 'Cama no tiene asignación activa'], 404);
        }

        $assignment->update(['released_at' => now()]);
        $bed->update(['status' => 'available']);

        return response()->json([
            'success' => true,
            'message' => 'Cama liberada correctamente',
        ]);
    }
}
