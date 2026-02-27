<?php

namespace App\Http\Controllers;

use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCalendarController extends Controller
{
    private function assertSuperAdmin(): void
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
    }

    public function index(Request $request)
    {
        $this->assertSuperAdmin();

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $guardias = Guardia::orderBy('name')->get();

        $calendarDays = GuardiaCalendarDay::with('guardia')
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn ($d) => $d->date->toDateString());

        return view('admin.calendario', compact('guardias', 'calendarDays', 'month', 'year', 'startOfMonth', 'endOfMonth'));
    }

    public function assignRange(Request $request)
    {
        $this->assertSuperAdmin();

        $data = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'guardia_id' => 'required|exists:guardias,id',
            'weekdays' => 'required|array|min:1',
            'weekdays.*' => 'integer|between:0,6',
        ]);

        $from = Carbon::parse($data['from'])->startOfDay();
        $to = Carbon::parse($data['to'])->startOfDay();

        $weekdays = collect($data['weekdays'])->map(fn ($d) => (int) $d)->unique()->values()->all();

        $cursor = $from->copy();
        while ($cursor->lessThanOrEqualTo($to)) {
            if (in_array($cursor->dayOfWeek, $weekdays, true)) {
                GuardiaCalendarDay::updateOrCreate(
                    ['date' => $cursor->toDateString()],
                    ['guardia_id' => $data['guardia_id']]
                );
            }
            $cursor->addDay();
        }

        return redirect()->route('admin.calendario', ['month' => $from->month, 'year' => $from->year])
            ->with('success', 'Calendario actualizado correctamente.');
    }

    public function generateRotation(Request $request)
    {
        $this->assertSuperAdmin();

        // Get actual count of guardias for dynamic validation
        $guardiaCount = Guardia::count();
        
        $data = $request->validate([
            'start_sunday' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_sunday',
            'guardia_ids' => 'required|array|size:' . $guardiaCount,
            'guardia_ids.*' => 'required|distinct|exists:guardias,id',
            'send_email' => 'nullable|boolean',
            'email_recipients' => 'nullable|string',
        ]);

        $startSunday = Carbon::parse($data['start_sunday'])->startOfDay();
        if (!$startSunday->isSunday()) {
            return back()->withErrors(['start_sunday' => 'La fecha de inicio debe ser un domingo.'])->withInput();
        }

        $endDate = isset($data['end_date']) && $data['end_date']
            ? Carbon::parse($data['end_date'])->startOfDay()
            : $startSunday->copy()->endOfYear()->startOfDay();

        $guardiaIds = array_values(array_map('intval', $data['guardia_ids']));
        $guardias = Guardia::whereIn('id', $guardiaIds)->get()->keyBy('id');

        $rows = [];
        $rotationSummary = [];
        $now = now();

        $weekIndex = 0;
        $weekStart = $startSunday->copy();
        while ($weekStart->lessThanOrEqualTo($endDate)) {
            $guardiaId = $guardiaIds[$weekIndex % count($guardiaIds)];
            $guardiaName = $guardias[$guardiaId]->name ?? 'Guardia ' . $guardiaId;

            // Track rotation for summary
            $weekKey = $weekStart->format('d/m/Y');
            $rotationSummary[] = [
                'week_start' => $weekKey,
                'week_number' => $weekIndex + 1,
                'guardia_name' => $guardiaName,
            ];

            for ($i = 0; $i < 7; $i++) {
                $date = $weekStart->copy()->addDays($i);
                if ($date->greaterThan($endDate)) {
                    break;
                }

                $rows[] = [
                    'date' => $date->toDateString(),
                    'guardia_id' => $guardiaId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (count($rows) >= 700) {
                DB::table('guardia_calendar_days')->upsert($rows, ['date'], ['guardia_id', 'updated_at']);
                $rows = [];
            }

            $weekStart->addWeek();
            $weekIndex++;
        }

        if (count($rows) > 0) {
            DB::table('guardia_calendar_days')->upsert($rows, ['date'], ['guardia_id', 'updated_at']);
        }

        // Send email if requested
        if (!empty($data['send_email']) && !empty($data['email_recipients'])) {
            $this->sendRotationEmail($rotationSummary, $data['email_recipients'], $startSunday, $endDate);
        }

        return redirect()->route('admin.calendario', ['month' => $startSunday->month, 'year' => $startSunday->year])
            ->with('success', 'Rotación generada correctamente.' . (!empty($data['send_email']) ? ' Se envió el resumen por correo.' : ''));
    }

    private function sendRotationEmail(array $rotationSummary, string $recipients, Carbon $startDate, Carbon $endDate): void
    {
        $emails = array_map('trim', explode(',', $recipients));
        $emails = array_filter($emails, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL));
        
        if (empty($emails)) {
            return;
        }

        $subject = 'Rotación Semanal de Guardias Generada - ' . $startDate->format('d/m/Y');
        
        // Build email body
        $lines = [
            'Se ha generado una nueva rotación semanal de guardias.',
            '',
            '**Período:** ' . $startDate->format('d/m/Y') . ' hasta ' . $endDate->format('d/m/Y'),
            '**Total de semanas:** ' . count($rotationSummary),
            '',
            '**Resumen de la rotación:**',
        ];

        foreach (array_slice($rotationSummary, 0, 20) as $week) {
            $lines[] = "• Semana {$week['week_number']} (desde {$week['week_start']}): {$week['guardia_name']}";
        }

        if (count($rotationSummary) > 20) {
            $lines[] = '... y ' . (count($rotationSummary) - 20) . ' semanas más';
        }

        $lines[] = '';
        $lines[] = 'Para ver el calendario completo, ingresa al sistema.';

        // Use SystemEmailService if available
        if (class_exists(\App\Services\SystemEmailService::class)) {
            \App\Services\SystemEmailService::send(
                type: 'rotation',
                subject: $subject,
                lines: $lines,
                sourceLabel: 'Calendario'
            );
        } else {
            // Fallback to direct mail
            $mailBody = implode("\n", $lines);
            foreach ($emails as $email) {
                try {
                    \Illuminate\Support\Facades\Mail::raw($mailBody, function($message) use ($email, $subject) {
                        $message->to($email)->subject($subject);
                    });
                } catch (\Exception $e) {
                    // Log error but don't stop execution
                    \Illuminate\Support\Facades\Log::error('Error enviando email de rotación: ' . $e->getMessage());
                }
            }
        }
    }
}
