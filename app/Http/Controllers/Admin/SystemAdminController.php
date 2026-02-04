<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CleaningAssignment;
use App\Models\Emergency;
use App\Models\GuardiaAttendanceRecord;
use App\Models\Novelty;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\StaffEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemAdminController extends Controller
{
    public function index()
    {
        return view('admin.system.index');
    }

    public function purge(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:novelties,shifts,emergencies,cleaning,staff_events,attendance_records,all',
            'confirm_text' => 'required|string',
        ]);

        if (trim((string) $validated['confirm_text']) !== 'CONFIRMAR') {
            return back()->withErrors(['confirm_text' => 'Debes escribir CONFIRMAR para ejecutar esta acción.']);
        }

        $action = $validated['action'];

        DB::transaction(function () use ($action) {
            if ($action === 'novelties' || $action === 'all') {
                Novelty::query()->delete();
            }

            if ($action === 'shifts' || $action === 'all') {
                ShiftUser::query()->delete();
                Shift::query()->delete();
            }

            if ($action === 'emergencies' || $action === 'all') {
                Emergency::query()->delete();
            }

            if ($action === 'cleaning' || $action === 'all') {
                CleaningAssignment::query()->delete();
            }

            if ($action === 'staff_events' || $action === 'all') {
                StaffEvent::query()->delete();
            }

            if ($action === 'attendance_records' || $action === 'all') {
                GuardiaAttendanceRecord::query()->delete();
            }
        });

        return back()->with('success', 'Operación ejecutada correctamente.');
    }
}
