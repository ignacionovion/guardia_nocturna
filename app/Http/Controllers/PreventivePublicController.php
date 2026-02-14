<?php

namespace App\Http\Controllers;

use App\Models\PreventiveEvent;
use App\Models\PreventiveShift;
use App\Models\PreventiveShiftAssignment;
use App\Models\PreventiveShiftAttendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PreventivePublicController extends Controller
{
    public function show(Request $request, string $token)
    {
        $event = PreventiveEvent::query()->where('public_token', $token)->firstOrFail();

        $now = Carbon::now($event->timezone);
        $shift = $this->resolveCurrentShift($event, $now);

        if (!$shift) {
            return view('preventivas.public', [
                'event' => $event,
                'shift' => null,
                'assignments' => collect(),
                'now' => $now,
            ]);
        }

        $assignments = PreventiveShiftAssignment::query()
            ->where('preventive_shift_id', $shift->id)
            ->with(['firefighter', 'attendance'])
            ->get()
            ->sortBy(function ($a) {
                return (string) ($a->firefighter?->apellido_paterno ?? '');
            })
            ->values();

        return view('preventivas.public', [
            'event' => $event,
            'shift' => $shift,
            'assignments' => $assignments,
            'now' => $now,
        ]);
    }

    public function confirm(Request $request, string $token)
    {
        $event = PreventiveEvent::query()->where('public_token', $token)->firstOrFail();

        $validated = $request->validate([
            'assignment_id' => ['required', 'exists:preventive_shift_assignments,id'],
        ]);

        $assignment = PreventiveShiftAssignment::query()
            ->with(['shift'])
            ->findOrFail((int) $validated['assignment_id']);

        if (!$assignment->shift || (int) $assignment->shift->preventive_event_id !== (int) $event->id) {
            abort(404);
        }

        $attendance = PreventiveShiftAttendance::query()->where('preventive_shift_assignment_id', $assignment->id)->first();
        if ($attendance) {
            return back()->with('warning', 'Ya registraste asistencia para este turno.');
        }

        PreventiveShiftAttendance::create([
            'preventive_shift_assignment_id' => $assignment->id,
            'status' => 'present',
            'confirmed_at' => now(),
            'confirm_ip' => $request->ip(),
            'confirm_user_agent' => substr((string) $request->userAgent(), 0, 1024),
        ]);

        return back()
            ->withInput(['assignment_id' => (string) $assignment->id])
            ->with('success', 'Asistencia registrada.');
    }

    private function resolveCurrentShift(PreventiveEvent $event, Carbon $now): ?PreventiveShift
    {
        $today = $now->toDateString();

        $shifts = PreventiveShift::query()
            ->where('preventive_event_id', $event->id)
            ->whereDate('shift_date', $today)
            ->orderBy('sort_order')
            ->get();

        foreach ($shifts as $shift) {
            $start = Carbon::parse($shift->shift_date->toDateString() . ' ' . $shift->start_time, $event->timezone);
            $end = Carbon::parse($shift->shift_date->toDateString() . ' ' . $shift->end_time, $event->timezone);

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            if ($now->greaterThanOrEqualTo($start) && $now->lessThan($end)) {
                return $shift;
            }
        }

        return null;
    }

}
