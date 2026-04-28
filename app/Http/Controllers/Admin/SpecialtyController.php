<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\SystemSetting;
use App\Traits\TenantAdminAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class SpecialtyController extends Controller
{
    use TenantAdminAuth;

    public function index()
    {
        $this->requireTenantAdmin();

        $specialties = Specialty::query()
            ->withCount('bomberos')
            ->orderByDesc('active')
            ->orderBy('name')
            ->get();

        return view('admin.specialties.index', compact('specialties'));
    }

    public function store(Request $request)
    {
        $this->requireTenantAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:specialties,name'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        Specialty::query()->create([
            'name' => trim($validated['name']),
            'slug' => $this->buildUniqueSlug($validated['name']),
            'icon' => null,
            'color' => trim((string) ($validated['color'] ?? '#334155')) ?: '#334155',
            'active' => true,
        ]);
        $this->markSpecialtiesCustomized();

        return $this->redirectToSpecialties('success', 'Especialidad creada correctamente.');
    }

    public function update(Request $request, string|int|Specialty $specialty)
    {
        $this->requireTenantAdmin();
        $specialty = $this->resolveSpecialty($specialty);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('specialties', 'name')->ignore($specialty->id)],
            'color' => ['nullable', 'string', 'max:20'],
            'active' => ['nullable', 'boolean'],
        ]);

        $newName = trim($validated['name']);
        $slug = $specialty->slug;

        if (Str::lower($newName) !== Str::lower($specialty->name)) {
            $slug = $this->buildUniqueSlug($newName, $specialty->id);
        }

        $specialty->update([
            'name' => $newName,
            'slug' => $slug,
            'icon' => null,
            'color' => trim((string) ($validated['color'] ?? $specialty->color)) ?: '#334155',
            'active' => $request->boolean('active', $specialty->active),
        ]);
        $this->markSpecialtiesCustomized();

        return $this->redirectToSpecialties('success', 'Especialidad actualizada.');
    }

    public function toggle(string|int|Specialty $specialty)
    {
        $this->requireTenantAdmin();
        $specialty = $this->resolveSpecialty($specialty);

        $specialty->update([
            'active' => !$specialty->active,
        ]);
        $this->markSpecialtiesCustomized();

        return $this->redirectToSpecialties('success', 'Estado de especialidad actualizado.');
    }

    public function destroy(string|int|Specialty $specialty)
    {
        $this->requireTenantAdmin();

        try {
            $specialty = $this->resolveSpecialty($specialty);
            $inUse = $specialty->bomberos()->exists();

            if ($inUse) {
                $specialty->update(['active' => false]);
                $this->markSpecialtiesCustomized();

                return $this->redirectToSpecialties(
                    'warning',
                    'La especialidad está en uso y fue desactivada en lugar de eliminarse.'
                );
            }

            $specialty->delete();
            $this->markSpecialtiesCustomized();

            return $this->redirectToSpecialties('success', 'Especialidad eliminada correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return $this->redirectToSpecialties(
                'error',
                'No se pudo eliminar la especialidad. Inténtalo nuevamente.'
            );
        }
    }

    private function buildUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'especialidad';
        $slug = $baseSlug;
        $i = 2;

        while (
            Specialty::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function resolveSpecialty(string|int|Specialty $specialty): Specialty
    {
        if ($specialty instanceof Specialty) {
            return $specialty;
        }

        return Specialty::query()->findOrFail((int) $specialty);
    }

    private function redirectToSpecialties(string $type = 'success', string $message = 'Operación realizada correctamente.')
    {
        return redirect('/admin/specialties')->with($type, $message);
    }

    private function markSpecialtiesCustomized(): void
    {
        try {
            SystemSetting::setValue('specialties_customized', '1');
        } catch (Throwable $e) {
            report($e);
        }
    }
}
