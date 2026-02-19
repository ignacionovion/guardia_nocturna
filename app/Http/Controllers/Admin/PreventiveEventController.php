<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bombero;
use App\Models\PreventiveEvent;
use App\Models\PreventiveShift;
use App\Models\PreventiveShiftAssignment;
use App\Models\PreventiveShiftAttendance;
use App\Models\PreventiveShiftTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PreventiveEventController extends Controller
{
    private function autoCloseExpiredEvents(?PreventiveEvent $specificEvent = null): void
    {
        $events = $specificEvent
            ? collect([$specificEvent])
            : PreventiveEvent::query()->where('status', 'active')->get();

        foreach ($events as $event) {
            if (!$event instanceof PreventiveEvent) {
                continue;
            }

            $status = $this->normalizedStatus($event);
            if ($status !== 'active') {
                continue;
            }

            $tz = (string) ($event->timezone ?? 'UTC');
            $maxEnd = PreventiveShiftTemplate::query()
                ->where('preventive_event_id', $event->id)
                ->max('end_time');

            $maxEnd = $maxEnd ? substr((string) $maxEnd, 0, 5) : '23:59';
            $endDate = $event->end_date?->toDateString();
            if (!$endDate) {
                continue;
            }

            try {
                $endsAt = Carbon::createFromFormat('Y-m-d H:i', $endDate . ' ' . $maxEnd, $tz);
            } catch (\Throwable $e) {
                continue;
            }

            if (Carbon::now($tz)->greaterThanOrEqualTo($endsAt)) {
                $event->status = 'closed';
                $event->save();
            }
        }
    }

    private function normalizedStatus(PreventiveEvent $event): string
    {
        $status = strtolower((string) ($event->status ?? 'draft'));
        return in_array($status, ['draft', 'active', 'closed'], true) ? $status : 'draft';
    }

    public function index()
    {
        $this->autoCloseExpiredEvents();
        $events = PreventiveEvent::query()->latest()->paginate(20);
        return view('admin.preventivas.index', compact('events'));
    }

    public function create()
    {
        return view('admin.preventivas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'timezone' => ['required', 'string', 'max:64'],
            'template' => ['required', 'array', 'min:1'],
            'template.*.start_time' => ['required', 'date_format:H:i'],
            'template.*.end_time' => ['required', 'date_format:H:i'],
            'template.*.label' => ['nullable', 'string', 'max:255'],
        ]);

        $event = DB::transaction(function () use ($validated) {
            $event = PreventiveEvent::create([
                'title' => $validated['title'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'timezone' => $validated['timezone'],
                'status' => 'draft',
                'public_token' => Str::random(40),
            ]);

            foreach (array_values($validated['template']) as $i => $row) {
                PreventiveShiftTemplate::create([
                    'preventive_event_id' => $event->id,
                    'sort_order' => $i,
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'label' => $row['label'] ?? null,
                ]);
            }

            $this->generateShiftsForEvent($event);

            return $event;
        });

        return redirect()->route('admin.preventivas.show', $event)->with('success', 'Preventiva creada correctamente.');
    }

    public function show(PreventiveEvent $event)
    {
        $this->autoCloseExpiredEvents($event);
        $event->load(['templates' => function ($q) {
            $q->orderBy('sort_order');
        }]);

        $shifts = PreventiveShift::query()
            ->where('preventive_event_id', $event->id)
            ->with(['assignments.firefighter', 'assignments.attendance'])
            ->orderBy('shift_date')
            ->orderBy('sort_order')
            ->get();

        $firefighters = Bombero::query()
            ->where(function ($q) {
                $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
            })
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get(['id', 'nombres', 'apellido_paterno', 'rut']);

        $shiftsByDate = $shifts->groupBy(fn ($s) => $s->shift_date->toDateString());

        return view('admin.preventivas.show', compact('event', 'shiftsByDate', 'firefighters'));
    }

    public function saveTemplates(Request $request, PreventiveEvent $event)
    {
        $status = $this->normalizedStatus($event);
        if (in_array($status, ['active', 'closed'], true)) {
            return back()->with('warning', 'La plantilla no se puede modificar cuando la preventiva está Activa o Cerrada.');
        }

        $validated = $request->validate([
            'template' => ['required', 'array', 'min:1'],
            'template.*.start_time' => ['required', 'date_format:H:i'],
            'template.*.end_time' => ['required', 'date_format:H:i'],
            'template.*.label' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $event) {
            PreventiveShiftTemplate::query()->where('preventive_event_id', $event->id)->delete();
            foreach (array_values($validated['template']) as $i => $row) {
                PreventiveShiftTemplate::create([
                    'preventive_event_id' => $event->id,
                    'sort_order' => $i,
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'label' => $row['label'] ?? null,
                ]);
            }

            PreventiveShift::query()->where('preventive_event_id', $event->id)->delete();
            $this->generateShiftsForEvent($event);
        });

        return back()->with('success', 'Plantilla actualizada y turnos regenerados.');
    }

    public function addAssignment(Request $request, PreventiveEvent $event)
    {
        $status = $this->normalizedStatus($event);
        if ($status === 'closed') {
            return back()->with('warning', 'La preventiva está Cerrada. No se pueden modificar asignaciones.');
        }

        $validated = $request->validate([
            'preventive_shift_id' => ['required', 'exists:preventive_shifts,id'],
            'bombero_id' => ['required', 'exists:bomberos,id'],
        ]);

        $shift = PreventiveShift::query()
            ->where('preventive_event_id', $event->id)
            ->where('id', $validated['preventive_shift_id'])
            ->firstOrFail();

        PreventiveShiftAssignment::query()->firstOrCreate([
            'preventive_shift_id' => $shift->id,
            'bombero_id' => (int) $validated['bombero_id'],
        ]);

        return back()->with('success', 'Asignación agregada.');
    }

    public function removeAssignment(PreventiveEvent $event, PreventiveShiftAssignment $assignment)
    {
        $status = $this->normalizedStatus($event);
        if ($status === 'closed') {
            return back()->with('warning', 'La preventiva está Cerrada. No se pueden modificar asignaciones.');
        }

        $assignment->load('shift');
        if (!$assignment->shift || (int) $assignment->shift->preventive_event_id !== (int) $event->id) {
            abort(404);
        }

        $assignment->delete();
        return back()->with('success', 'Asignación eliminada.');
    }

    public function toggleAttendance(Request $request, PreventiveEvent $event, PreventiveShiftAssignment $assignment)
    {
        $this->autoCloseExpiredEvents($event);
        $status = $this->normalizedStatus($event);
        if ($status === 'closed') {
            return back()->with('warning', 'La preventiva está Cerrada. No se puede modificar asistencia.');
        }

        $assignment->load(['shift', 'attendance']);
        if (!$assignment->shift || (int) $assignment->shift->preventive_event_id !== (int) $event->id) {
            abort(404);
        }

        if ($assignment->attendance) {
            $assignment->attendance->delete();
            return back()->with('success', 'Asistencia removida.');
        }

        PreventiveShiftAttendance::create([
            'preventive_shift_assignment_id' => (int) $assignment->id,
            'status' => 'present',
            'confirmed_at' => now(),
            'confirm_ip' => $request->ip(),
            'confirm_user_agent' => substr('admin-manual', 0, 1024),
        ]);

        return back()->with('success', 'Asistencia marcada manualmente.');
    }

    public function pdf(PreventiveEvent $event)
    {
        $event->load(['templates', 'shifts.assignments.firefighter']);

        $dates = collect();
        $start = $event->start_date->copy();
        $end = $event->end_date->copy();
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dates->push($d->copy());
        }

        $templates = $event->templates()->orderBy('sort_order')->get();
        $shifts = PreventiveShift::query()
            ->where('preventive_event_id', $event->id)
            ->with(['assignments.firefighter'])
            ->get();

        $shiftMap = $shifts->keyBy(fn ($s) => $s->shift_date->toDateString() . '|' . (int) $s->sort_order);

        $pdf = Pdf::loadView('admin.preventivas.pdf', [
            'event' => $event,
            'dates' => $dates,
            'templates' => $templates,
            'shiftMap' => $shiftMap,
        ])->setPaper('a4', 'landscape');

        $filename = 'preventivas_' . $event->id . '.pdf';
        return $pdf->download($filename);
    }

    public function qr(PreventiveEvent $event)
    {
        $this->autoCloseExpiredEvents($event);
        $status = $this->normalizedStatus($event);
        if ($status !== 'active') {
            return back()->with('warning', 'Debes activar la preventiva para habilitar el QR.');
        }

        $url = route('preventivas.public.show', $event->public_token);
        $svg = QrCode::format('svg')->size(220)->margin(1)->generate($url);

        return response()->view('admin.preventivas.qr', [
            'event' => $event,
            'url' => $url,
            'svg' => $svg,
        ]);
    }

    public function qrPrint(PreventiveEvent $event)
    {
        $this->autoCloseExpiredEvents($event);
        $status = $this->normalizedStatus($event);
        if ($status !== 'active') {
            return back()->with('warning', 'Debes activar la preventiva para imprimir el QR.');
        }

        $url = route('preventivas.public.show', $event->public_token);
        $svg = QrCode::format('svg')->size(280)->margin(2)->generate($url);

        return response()->view('admin.preventivas.qr_print', [
            'event' => $event,
            'url' => $url,
            'svg' => $svg,
        ]);
    }

    public function regenerateQr(Request $request, PreventiveEvent $event)
    {
        $status = $this->normalizedStatus($event);
        if ($status === 'closed') {
            return back()->with('warning', 'La preventiva está Cerrada. No se puede regenerar el QR.');
        }

        $event->public_token = Str::random(40);
        $event->save();

        return redirect()
            ->route('admin.preventivas.qr', $event)
            ->with('success', 'QR regenerado correctamente. El link anterior quedó invalidado.');
    }

    public function activate(Request $request, PreventiveEvent $event)
    {
        $status = $this->normalizedStatus($event);
        if ($status === 'closed') {
            return back()->with('warning', 'La preventiva está Cerrada.');
        }

        $event->status = 'active';
        $event->save();

        return back()->with('success', 'Preventiva activada correctamente.');
    }

    public function close(Request $request, PreventiveEvent $event)
    {
        $event->status = 'closed';
        $event->save();

        return back()->with('success', 'Preventiva cerrada correctamente.');
    }

    public function setDraft(Request $request, PreventiveEvent $event)
    {
        $event->status = 'draft';
        $event->save();

        return back()->with('success', 'Preventiva reabierta en modo Borrador.');
    }

    /**
     * Muestra el reporte detallado del evento preventivo
     */
    public function report(PreventiveEvent $event)
    {
        $event->load(['templates', 'shifts.assignments.firefighter', 'shifts.assignments.attendance']);

        $shifts = PreventiveShift::query()
            ->where('preventive_event_id', $event->id)
            ->with(['assignments.firefighter', 'assignments.attendance'])
            ->orderBy('shift_date')
            ->orderBy('sort_order')
            ->get();

        // Estadísticas
        $totalAssignments = 0;
        $totalAttendance = 0;
        $totalRefuerzos = 0;
        $totalReemplazos = 0;

        foreach ($shifts as $shift) {
            foreach ($shift->assignments as $assignment) {
                $totalAssignments++;
                if ($assignment->attendance) {
                    $totalAttendance++;
                }
                if ($assignment->es_refuerzo) {
                    $totalRefuerzos++;
                }
                // Consideramos reemplazo si tiene entrada_hora pero no estaba en asignaciones originales
                if ($assignment->entrada_hora && !$assignment->attendance?->confirmed_at?->equalTo($assignment->entrada_hora)) {
                    $totalReemplazos++;
                }
            }
        }

        return view('admin.preventivas.report', compact(
            'event',
            'shifts',
            'totalAssignments',
            'totalAttendance',
            'totalRefuerzos',
            'totalReemplazos'
        ));
    }

    /**
     * Exporta el reporte a Excel
     */
    public function reportExcel(PreventiveEvent $event)
    {
        $event->load(['shifts.assignments.firefighter', 'shifts.assignments.attendance']);

        $shifts = PreventiveShift::query()
            ->where('preventive_event_id', $event->id)
            ->with(['assignments.firefighter', 'assignments.attendance'])
            ->orderBy('shift_date')
            ->orderBy('sort_order')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="reporte_preventiva_' . $event->id . '.csv"',
        ];

        $callback = function() use ($event, $shifts) {
            $output = fopen('php://output', 'w');
            
            // BOM para Excel
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Título
            fputcsv($output, ['REPORTE DE PREVENTIVA']);
            fputcsv($output, ['Evento:', $event->title]);
            fputcsv($output, ['Período:', $event->start_date->format('d/m/Y') . ' - ' . $event->end_date->format('d/m/Y')]);
            fputcsv($output, ['Zona horaria:', $event->timezone]);
            fputcsv($output, ['Estado:', $event->status]);
            fputcsv($output, []);

            // Encabezados
            fputcsv($output, [
                'Fecha',
                'Turno',
                'Horario',
                'Apellidos',
                'Nombres',
                'RUT',
                'Cargo',
                'Tipo',
                'Hora Entrada',
                'Asistencia Confirmada',
                'Hora Confirmación'
            ]);

            foreach ($shifts as $shift) {
                foreach ($shift->assignments as $assignment) {
                    $f = $assignment->firefighter;
                    fputcsv($output, [
                        $shift->shift_date->format('d/m/Y'),
                        $shift->label ?: 'Turno ' . ($shift->sort_order + 1),
                        substr($shift->start_time, 0, 5) . ' - ' . substr($shift->end_time, 0, 5),
                        $f?->apellido_paterno ?? 'N/A',
                        $f?->nombres ?? 'N/A',
                        $f?->rut ?? 'N/A',
                        $f?->cargo_texto ?? 'N/A',
                        $assignment->es_refuerzo ? 'REFUERZO' : 'TITULAR',
                        $assignment->entrada_hora?->format('H:i:s') ?? ($assignment->attendance?->confirmed_at?->format('H:i:s') ?? ''),
                        $assignment->attendance ? 'SÍ' : 'NO',
                        $assignment->attendance?->confirmed_at?->format('d/m/Y H:i:s') ?? ''
                    ]);
                }
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporta el reporte a PDF
     */
    public function reportPdf(PreventiveEvent $event)
    {
        $event->load(['shifts.assignments.firefighter', 'shifts.assignments.attendance']);

        $shifts = PreventiveShift::query()
            ->where('preventive_event_id', $event->id)
            ->with(['assignments.firefighter', 'assignments.attendance'])
            ->orderBy('shift_date')
            ->orderBy('sort_order')
            ->get();

        // Estadísticas
        $totalAssignments = 0;
        $totalAttendance = 0;
        $totalRefuerzos = 0;

        foreach ($shifts as $shift) {
            foreach ($shift->assignments as $assignment) {
                $totalAssignments++;
                if ($assignment->attendance) {
                    $totalAttendance++;
                }
                if ($assignment->es_refuerzo) {
                    $totalRefuerzos++;
                }
            }
        }

        $pdf = Pdf::loadView('admin.preventivas.report_pdf', compact(
            'event',
            'shifts',
            'totalAssignments',
            'totalAttendance',
            'totalRefuerzos'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('reporte_preventiva_' . $event->id . '.pdf');
    }

    private function generateShiftsForEvent(PreventiveEvent $event): void
    {
        $templates = PreventiveShiftTemplate::query()
            ->where('preventive_event_id', $event->id)
            ->orderBy('sort_order')
            ->get();

        $start = $event->start_date->copy();
        $end = $event->end_date->copy();

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            foreach ($templates as $tpl) {
                PreventiveShift::create([
                    'preventive_event_id' => $event->id,
                    'template_id' => $tpl->id,
                    'shift_date' => $d->toDateString(),
                    'start_time' => (string) $tpl->start_time,
                    'end_time' => (string) $tpl->end_time,
                    'sort_order' => (int) $tpl->sort_order,
                    'label' => $tpl->label,
                ]);
            }
        }
    }
}
