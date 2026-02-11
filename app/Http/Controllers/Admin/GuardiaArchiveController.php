<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\GuardiaArchive;
use App\Models\GuardiaArchiveItem;
use Illuminate\Http\Request;

class GuardiaArchiveController extends Controller
{
    private function authorizeGuardiaAccess(Guardia $guardia): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'No autorizado.');
        }

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        if ($user->role === 'guardia' && (int) $user->guardia_id !== (int) $guardia->id) {
            abort(403, 'No autorizado.');
        }
    }

    public function index(Request $request, Guardia $guardia)
    {
        $this->authorizeGuardiaAccess($guardia);

        $archives = GuardiaArchive::query()
            ->where('guardia_id', $guardia->id)
            ->orderByDesc('archived_at')
            ->paginate(20);

        return view('admin.guardias.history.index', compact('guardia', 'archives'));
    }

    public function show(Request $request, Guardia $guardia, GuardiaArchive $archive)
    {
        $this->authorizeGuardiaAccess($guardia);

        if ((int) $archive->guardia_id !== (int) $guardia->id) {
            abort(404);
        }

        $filters = $request->validate([
            'firefighter_id' => 'nullable|integer|exists:bomberos,id',
            'entity_type' => 'nullable|string|max:50',
        ]);

        $itemsQuery = GuardiaArchiveItem::query()
            ->where('guardia_archive_id', $archive->id)
            ->orderByDesc('id');

        if (!empty($filters['firefighter_id'])) {
            $itemsQuery->where('firefighter_id', (int) $filters['firefighter_id']);
        }

        if (!empty($filters['entity_type'])) {
            $itemsQuery->where('entity_type', $filters['entity_type']);
        }

        $items = $itemsQuery->paginate(50)->withQueryString();

        $firefighters = Bombero::query()
            ->where('guardia_id', $guardia->id)
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get();

        $entityTypes = GuardiaArchiveItem::query()
            ->where('guardia_archive_id', $archive->id)
            ->select('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');

        return view('admin.guardias.history.show', compact('guardia', 'archive', 'items', 'firefighters', 'entityTypes', 'filters'));
    }
}
