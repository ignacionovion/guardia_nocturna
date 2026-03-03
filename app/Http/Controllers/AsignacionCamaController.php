<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\MapaBomberoUsuarioLegacy;
use App\Services\SystemEmailService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsignacionCamaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assignments = BedAssignment::with(['bed', 'firefighter', 'user'])->latest()->paginate(20);
        return response()->json($assignments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bed_id' => 'required|exists:beds,id',
            'firefighter_id' => 'required|exists:bomberos,id',
            'notes' => 'nullable|string',
        ]);

        // Evitar pegarle a information_schema en cada request.
        // Si en el futuro agregas/eliminas la columna, reinicia PHP-FPM o el servidor para limpiar cache.
        static $hasUserIdColumn = null;
        if ($hasUserIdColumn === null) {
            $hasUserIdColumn = Schema::hasColumn('bed_assignments', 'user_id');
        }

        // Resolver legacy user_id una sola vez (en tu código original se hacía 2 veces)
        $legacyUserId = null;
        if ($hasUserIdColumn) {
            $legacyUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', $validated['firefighter_id'])->value('user_id');
        }

        try {
            // Transacción + lock para evitar condiciones de carrera (2 guardias asignando a la vez)
            $assignment = DB::transaction(function () use ($validated, $legacyUserId) {
                // Lock a la cama para que no la asignen en paralelo
                $bed = Bed::whereKey($validated['bed_id'])->lockForUpdate()->firstOrFail();

                if ($bed->status !== 'available') {
                    // Lanzamos excepción controlada para salir de la transacción
                    throw new \RuntimeException('La cama no está disponible.');
                }

                // Validación: Verificar si el voluntario (o su usuario legacy) ya tiene una cama activa
                $existingAssignment = BedAssignment::query()
                    ->whereNull('released_at')
                    ->where(function ($q) use ($validated, $legacyUserId) {
                        $q->where('firefighter_id', $validated['firefighter_id']);
                        if ($legacyUserId) {
                            $q->orWhere('user_id', $legacyUserId);
                        }
                    })
                    ->with('bed')
                    ->first();

                if ($existingAssignment) {
                    throw new \RuntimeException('Este voluntario ya tiene asignada la cama #' . ($existingAssignment->bed->number ?? $existingAssignment->bed_id));
                }

                $data = [
                    'bed_id' => $validated['bed_id'],
                    'firefighter_id' => $validated['firefighter_id'],
                    'notes' => $validated['notes'] ?? null,
                    'assigned_at' => now(),
                ];

                if ($legacyUserId) {
                    $data['user_id'] = $legacyUserId;
                }

                // Crear asignación
                $assignment = BedAssignment::create($data);
                $assignment->load(['bed', 'firefighter']);

                // Actualizar estado de la cama
                $bed->update(['status' => 'occupied']);

                return $assignment;
            }, 3); // 3 reintentos si hay deadlock

            return redirect()->route('camas')->with('success', 'Cama asignada correctamente.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['msg' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('Error asignando cama: ' . $e->getMessage(), [
                'bed_id' => $validated['bed_id'] ?? null,
                'firefighter_id' => $validated['firefighter_id'] ?? null,
            ]);
            return back()->withErrors(['msg' => 'Ocurrió un error al asignar la cama. Revisa los logs.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $assignment = BedAssignment::with(['bed', 'firefighter', 'user'])->findOrFail($id);
        return response()->json($assignment);
    }

    /**
     * Update the specified resource in storage.
     * Used mainly to release the bed (update released_at)
     */
    public function update(Request $request, string $id)
    {
        $assignment = BedAssignment::findOrFail($id);

        if ($request->has('release') && $request->release) {
             $assignment->update([
                 'released_at' => now(),
             ]);

             $assignment->load(['bed', 'firefighter']);
             $assignment->bed->update(['status' => 'available']);

             return redirect()->route('camas')->with('success', 'Cama liberada correctamente.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $assignment->update($validated);

        return redirect()->route('camas')->with('success', 'Asignación actualizada.');
    }

    public function markMaintenance(Request $request, Bed $bed)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        if ($bed->status === 'occupied') {
            return back()->withErrors(['msg' => 'No puedes poner en mantención una cama ocupada.']);
        }

        $bed->update([
            'status' => 'maintenance',
            'description' => $bed->description ?: 'En mantención',
        ]);

        return redirect()->route('camas')->with('success', 'Cama marcada en mantención.');
    }

    public function markAvailable(Request $request, Bed $bed)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        if ($bed->currentAssignment) {
            return back()->withErrors(['msg' => 'No puedes habilitar una cama con ocupación activa.']);
        }

        $bed->update([
            'status' => 'available',
        ]);

        return redirect()->route('camas')->with('success', 'Cama habilitada como disponible.');
    }

    /**
     * Send bed assignments report via email with PDF attachment
     */
    public function sendReportEmail(Request $request)
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['super_admin', 'capitania', 'guardia'])) {
            return response()->json(['ok' => false, 'message' => 'No autorizado'], 403);
        }

        try {
            // Obtener todas las camas con sus asignaciones actuales
            $beds = Bed::with(['currentAssignment.firefighter'])
                ->orderBy('number')
                ->get();

            $availableCount = $beds->where('status', 'available')->count();
            $occupiedCount = $beds->where('status', 'occupied')->count();
            $maintenanceCount = $beds->where('status', 'maintenance')->count();

            // Generar PDF usando dompdf
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.beds_report', [
                'beds' => $beds,
                'availableCount' => $availableCount,
                'occupiedCount' => $occupiedCount,
                'maintenanceCount' => $maintenanceCount,
                'generatedAt' => now(),
                'generatedBy' => $user->name ?? 'Sistema',
            ]);

            $pdf->setPaper('A4', 'portrait');
            $pdfContent = $pdf->output();

            // Preparar datos para el email
            $subject = '📋 Reporte de Camas - ' . now()->format('d/m/Y H:i');
            $lines = [
                'Resumen de Camas:',
                '',
                '✅ Disponibles: ' . $availableCount,
                '🛏️ Ocupadas: ' . $occupiedCount,
                '🔧 En Mantención: ' . $maintenanceCount,
                '',
                'Total de camas: ' . $beds->count(),
                '',
                'Reporte generado por: ' . ($user->name ?? 'Sistema'),
                'Fecha y hora: ' . now()->format('d/m/Y H:i'),
            ];

            // Enviar email con PDF adjunto
            SystemEmailService::send(
                type: 'beds',
                subject: $subject,
                lines: $lines,
                actorEmail: $user?->email,
                senderName: $user?->name,
                senderRole: $user?->role,
                sourceLabel: 'Sistema de Asignación de Camas',
                fileAttachments: [
                    [
                        'content' => $pdfContent,
                        'filename' => 'reporte_camas_' . now()->format('Y-m-d_H-i') . '.pdf',
                        'mime' => 'application/pdf',
                    ],
                ]
            );

            return response()->json([
                'ok' => true,
                'message' => 'Reporte enviado correctamente',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error sending bed report email: ' . $e->getMessage(), [
                'user_id' => $user?->id,
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Error al generar o enviar el reporte: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         // Optional: Delete assignment history? Preferably not.
         return response()->json(['message' => 'Cannot delete assignments history.'], 403);
    }
}
