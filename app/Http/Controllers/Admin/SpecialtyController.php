<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Traits\TenantAdminAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        Specialty::query()->create([
            'name' => trim($validated['name']),
            'slug' => $this->buildUniqueSlug($validated['name']),
            'icon' => trim((string) ($validated['icon'] ?? 'shield')) ?: 'shield',
            'color' => trim((string) ($validated['color'] ?? '#334155')) ?: '#334155',
            'active' => true,
        ]);

        return redirect()->route('admin.specialties.index')->with('success', 'Especialidad creada correctamente.');
    }

    public function update(Request $request, Specialty $specialty)
    {
        $this->requireTenantAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('specialties', 'name')->ignore($specialty->id)],
            'icon' => ['nullable', 'string', 'max:50'],
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
            'icon' => trim((string) ($validated['icon'] ?? $specialty->icon)) ?: 'shield',
            'color' => trim((string) ($validated['color'] ?? $specialty->color)) ?: '#334155',
            'active' => $request->boolean('active', $specialty->active),
        ]);

        return redirect()->route('admin.specialties.index')->with('success', 'Especialidad actualizada.');
    }

    public function toggle(Specialty $specialty)
    {
        $this->requireTenantAdmin();

        $specialty->update([
            'active' => !$specialty->active,
        ]);

        return redirect()->route('admin.specialties.index')->with('success', 'Estado de especialidad actualizado.');
    }

    public function destroy(Specialty $specialty)
    {
        $this->requireTenantAdmin();

        $inUse = $specialty->bomberos()->exists();

        if ($inUse) {
            $specialty->update(['active' => false]);

            return redirect()->route('admin.specialties.index')
                ->with('warning', 'La especialidad está en uso y fue desactivada en lugar de eliminarse.');
        }

        $specialty->delete();

        return redirect()->route('admin.specialties.index')->with('success', 'Especialidad eliminada correctamente.');
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
}
