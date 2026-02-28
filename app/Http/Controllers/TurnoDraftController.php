<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\TurnoSessionItem;
use App\Services\TurnoDraftService;
use Illuminate\Http\Request;

class TurnoDraftController extends Controller
{
    private function assertCanEdit(TurnoDraftService $service): void
    {
        if (!$service->isEditableNow()) {
            abort(403, 'Edición bloqueada fuera del horario 22:00-07:00.');
        }
    }

    private function resolveUserGuardiaOrAbort(): Guardia
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        if ($user->guardia_id) {
            $g = Guardia::find($user->guardia_id);
            if ($g) {
                return $g;
            }
        }

        abort(403, 'Cuenta sin guardia asignada.');
    }

    public function current(Request $request, TurnoDraftService $service)
    {
        $guardia = $this->resolveUserGuardiaOrAbort();
        $session = $service->getOrCreateDraftForGuardia($guardia, auth()->id());
        $items = $session->items()->get()->keyBy('firefighter_id');

        return response()->json([
            'ok' => true,
            'editable' => $service->isEditableNow(),
            'session' => [
                'id' => $session->id,
                'guardia_id' => $session->guardia_id,
                'operational_date' => optional($session->operational_date)->toDateString(),
                'opened_at' => optional($session->opened_at)->toISOString(),
                'close_at' => optional($session->close_at)->toISOString(),
                'status' => $session->status,
            ],
            'items' => $items->map(function ($i) {
                return [
                    'firefighter_id' => (int) $i->firefighter_id,
                    'included' => (bool) ($i->included ?? true),
                    'removed_at' => optional($i->removed_at)->toISOString(),
                    'attendance_status' => $i->attendance_status,
                    'confirm_token' => $i->confirm_token,
                    'confirmed_at' => optional($i->confirmed_at)->toISOString(),
                    'bed_id' => $i->bed_id ? (int) $i->bed_id : null,
                ];
            })->values(),
        ]);
    }

    public function upsertItem(Request $request, TurnoDraftService $service)
    {
        $this->assertCanEdit($service);

        $guardia = $this->resolveUserGuardiaOrAbort();
        $session = $service->getOrCreateDraftForGuardia($guardia, auth()->id());

        $data = $request->validate([
            'firefighter_id' => ['required', 'integer', 'exists:bomberos,id'],
            'attendance_status' => ['required', 'string', 'max:30'],
        ]);

        $firefighter = Bombero::findOrFail((int) $data['firefighter_id']);
        if ((int) $firefighter->guardia_id !== (int) $guardia->id) {
            abort(403, 'Bombero no pertenece a la guardia.');
        }

        // Si se cambia el estado, se invalida confirmación y cama.
        TurnoSessionItem::updateOrCreate(
            [
                'turno_session_id' => $session->id,
                'firefighter_id' => (int) $firefighter->id,
            ],
            [
                'included' => true,
                'removed_at' => null,
                'attendance_status' => strtolower((string) $data['attendance_status']),
                'confirm_token' => null,
                'confirmed_at' => null,
                'confirmed_by_user_id' => null,
                'bed_id' => null,
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function persistConfirmation(Request $request, TurnoDraftService $service)
    {
        $this->assertCanEdit($service);

        $guardia = $this->resolveUserGuardiaOrAbort();
        $session = $service->getOrCreateDraftForGuardia($guardia, auth()->id());

        $data = $request->validate([
            'firefighter_id' => ['required', 'integer', 'exists:bomberos,id'],
            'confirm_token' => ['required', 'string', 'max:255'],
        ]);

        $firefighter = Bombero::findOrFail((int) $data['firefighter_id']);
        if ((int) $firefighter->guardia_id !== (int) $guardia->id) {
            abort(403, 'Bombero no pertenece a la guardia.');
        }

        TurnoSessionItem::updateOrCreate(
            [
                'turno_session_id' => $session->id,
                'firefighter_id' => (int) $firefighter->id,
            ],
            [
                'included' => true,
                'removed_at' => null,
                'attendance_status' => $firefighter->estado_asistencia,
                'confirm_token' => (string) $data['confirm_token'],
                'confirmed_at' => now(),
                'confirmed_by_user_id' => auth()->id(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function assignBed(Request $request, TurnoDraftService $service)
    {
        $this->assertCanEdit($service);

        $guardia = $this->resolveUserGuardiaOrAbort();
        $session = $service->getOrCreateDraftForGuardia($guardia, auth()->id());

        $data = $request->validate([
            'firefighter_id' => ['required', 'integer', 'exists:bomberos,id'],
            'bed_id' => ['required', 'integer', 'exists:beds,id'],
        ]);

        $firefighter = Bombero::findOrFail((int) $data['firefighter_id']);
        if ((int) $firefighter->guardia_id !== (int) $guardia->id) {
            abort(403, 'Bombero no pertenece a la guardia.');
        }

        $item = TurnoSessionItem::query()
            ->where('turno_session_id', $session->id)
            ->where('firefighter_id', (int) $firefighter->id)
            ->first();

        if (!$item || !$item->confirmed_at || !$item->confirm_token) {
            return response()->json(['ok' => false, 'message' => 'Solo se puede asignar cama a bomberos confirmados.'], 422);
        }

        $bed = Bed::findOrFail((int) $data['bed_id']);

        $item->update([
            'bed_id' => $bed->id,
        ]);

        return response()->json(['ok' => true]);
    }

    public function seedItems(Request $request, TurnoDraftService $service)
    {
        $this->assertCanEdit($service);

        $guardia = $this->resolveUserGuardiaOrAbort();
        $session = $service->getOrCreateDraftForGuardia($guardia, auth()->id());

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.firefighter_id' => ['required', 'integer', 'exists:bomberos,id'],
            'items.*.attendance_status' => ['required', 'string', 'max:30'],
        ]);

        $items = (array) ($data['items'] ?? []);

        foreach ($items as $it) {
            $firefighterId = (int) ($it['firefighter_id'] ?? 0);
            $status = strtolower((string) ($it['attendance_status'] ?? ''));
            if ($firefighterId <= 0 || $status === '') {
                continue;
            }

            $firefighter = Bombero::find($firefighterId);
            if (!$firefighter) {
                continue;
            }
            if ((int) $firefighter->guardia_id !== (int) $guardia->id) {
                continue;
            }

            TurnoSessionItem::updateOrCreate(
                [
                    'turno_session_id' => $session->id,
                    'firefighter_id' => $firefighterId,
                ],
                [
                    'included' => true,
                    'removed_at' => null,
                    'attendance_status' => $status,
                ]
            );
        }

        return response()->json(['ok' => true]);
    }
}
