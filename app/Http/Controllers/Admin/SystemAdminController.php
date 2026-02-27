<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdministradorController;
use App\Http\Controllers\Controller;
use App\Models\Bombero;
use App\Models\CleaningAssignment;
use App\Models\Emergency;
use App\Models\Guardia;
use App\Models\GuardiaAttendanceRecord;
use App\Models\GuardiaCalendarDay;
use App\Models\Novelty;
use App\Models\ReemplazoBombero;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\StaffEvent;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemAdminController extends Controller
{
    public function index()
    {
        $guardias = Guardia::query()->orderBy('name')->get();

        $settings = [
            'attendance_enable_time' => SystemSetting::getValue('attendance_enable_time', '21:00'),
            'attendance_disable_time' => SystemSetting::getValue('attendance_disable_time', '10:00'),
            'guardia_constitution_weekday_time' => SystemSetting::getValue('guardia_constitution_weekday_time', '23:00'),
            'guardia_constitution_sunday_time' => SystemSetting::getValue('guardia_constitution_sunday_time', '22:00'),
            'guardia_daily_end_time' => SystemSetting::getValue('guardia_daily_end_time', '07:00'),
            'guardia_week_transition_time' => SystemSetting::getValue('guardia_week_transition_time', '18:00'),
            'guardia_week_cleanup_time' => SystemSetting::getValue('guardia_week_cleanup_time', '18:00'),
            'guardia_schedule_tz' => SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone'))),

            'mail_from_address' => SystemSetting::getValue('mail_from_address', 'app@germaniatemuco.cl'),
            'mail_from_name' => SystemSetting::getValue('mail_from_name', config('app.name', 'AppGuardia')),
            'mail_recipients' => SystemSetting::getValue('mail_recipients', 'ignacio.n12@gmail.com'),
            'mail_allowed_trigger_emails' => SystemSetting::getValue('mail_allowed_trigger_emails', ''),
            'mail_enabled_cleaning' => SystemSetting::getValue('mail_enabled_cleaning', '0'),
            'mail_enabled_novelty' => SystemSetting::getValue('mail_enabled_novelty', '0'),
            'mail_enabled_academy' => SystemSetting::getValue('mail_enabled_academy', '0'),
            'mail_enabled_beds' => SystemSetting::getValue('mail_enabled_beds', '0'),
            'mail_enabled_planilla' => SystemSetting::getValue('mail_enabled_planilla', '0'),
            'mail_enabled_rotation' => SystemSetting::getValue('mail_enabled_rotation', '0'),
        ];

        return view('admin.system.index', compact('guardias', 'settings'));
    }

    public function saveSchedule(Request $request)
    {
        $validated = $request->validate([
            'attendance_enable_time' => ['required', 'date_format:H:i'],
            'attendance_disable_time' => ['required', 'date_format:H:i'],
            'guardia_constitution_weekday_time' => ['required', 'date_format:H:i'],
            'guardia_constitution_sunday_time' => ['required', 'date_format:H:i'],
            'guardia_daily_end_time' => ['required', 'date_format:H:i'],
            'guardia_week_transition_time' => ['required', 'date_format:H:i'],
            'guardia_week_cleanup_time' => ['required', 'date_format:H:i'],
            'guardia_schedule_tz' => ['required', 'string', 'max:64'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                SystemSetting::setValue($key, (string) $value);
            }
        });

        return back()->with('success', 'Horarios del sistema actualizados correctamente.');
    }

    public function saveMailSettings(Request $request)
    {
        $validated = $request->validate([
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'mail_recipients' => ['required', 'string', 'max:5000'],
            'mail_allowed_trigger_emails' => ['nullable', 'string', 'max:5000'],
            'mail_enabled_cleaning' => ['nullable', 'boolean'],
            'mail_enabled_novelty' => ['nullable', 'boolean'],
            'mail_enabled_academy' => ['nullable', 'boolean'],
            'mail_enabled_beds' => ['nullable', 'boolean'],
            'mail_enabled_planilla' => ['nullable', 'boolean'],
            'mail_enabled_rotation' => ['nullable', 'boolean'],
        ]);

        $data = $validated;
        $data['mail_enabled_cleaning'] = $request->has('mail_enabled_cleaning') ? '1' : '0';
        $data['mail_enabled_novelty'] = $request->has('mail_enabled_novelty') ? '1' : '0';
        $data['mail_enabled_academy'] = $request->has('mail_enabled_academy') ? '1' : '0';
        $data['mail_enabled_beds'] = $request->has('mail_enabled_beds') ? '1' : '0';
        $data['mail_enabled_planilla'] = $request->has('mail_enabled_planilla') ? '1' : '0';
        $data['mail_enabled_rotation'] = $request->has('mail_enabled_rotation') ? '1' : '0';

        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                SystemSetting::setValue($key, (string) $value);
            }
        });

        return back()->with('success', 'Configuración de correos actualizada correctamente.');
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

    public function clearGuardias(Request $request)
    {
        $validated = $request->validate([
            'scope' => 'required|string|in:one,all',
            'guardia_id' => 'nullable|integer|exists:guardias,id',
            'confirm_text' => 'required|string',
        ]);

        if (trim((string) $validated['confirm_text']) !== 'CONFIRMAR') {
            return back()->withErrors(['confirm_text' => 'Debes escribir CONFIRMAR para ejecutar esta acción.']);
        }

        $scope = $validated['scope'];
        $guardiaId = $scope === 'one' ? (int) ($validated['guardia_id'] ?? 0) : null;

        if ($scope === 'one' && !$guardiaId) {
            return back()->withErrors(['guardia_id' => 'Debes seleccionar una guardia o elegir "todas".']);
        }

        DB::transaction(function () use ($scope, $guardiaId) {
            if ($scope === 'all') {
                Bombero::query()->update(['guardia_id' => null]);
                ShiftUser::query()->delete();
                GuardiaAttendanceRecord::query()->delete();
                GuardiaCalendarDay::query()->delete();
                ReemplazoBombero::query()->delete();
                Emergency::query()->delete();
                StaffEvent::query()->delete();
                CleaningAssignment::query()->delete();
                Novelty::query()->delete();
                return;
            }

            $firefighterIds = Bombero::query()
                ->where('guardia_id', $guardiaId)
                ->pluck('id');

            Bombero::query()->where('guardia_id', $guardiaId)->update(['guardia_id' => null]);
            ShiftUser::query()->where('guardia_id', $guardiaId)->delete();
            GuardiaAttendanceRecord::query()->where('guardia_id', $guardiaId)->delete();
            GuardiaCalendarDay::query()->where('guardia_id', $guardiaId)->delete();
            ReemplazoBombero::query()->where('guardia_id', $guardiaId)->delete();
            Emergency::query()->where('guardia_id', $guardiaId)->delete();

            if ($firefighterIds->isNotEmpty()) {
                StaffEvent::query()
                    ->whereIn('firefighter_id', $firefighterIds)
                    ->orWhereIn('replacement_firefighter_id', $firefighterIds)
                    ->delete();

                CleaningAssignment::query()->whereIn('firefighter_id', $firefighterIds)->delete();
                Novelty::query()->whereIn('firefighter_id', $firefighterIds)->delete();
            }
        });

        return back()->with('success', 'Guardias vaciadas correctamente.');
    }
}
