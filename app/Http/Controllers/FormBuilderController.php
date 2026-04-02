<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FormBuilderController extends Controller
{
    public function index()
    {
        $templates = FormTemplate::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('forms.builder.index', compact('templates'));
    }

    public function create()
    {
        return view('forms.builder.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'estructura' => 'required|array|min:1',
            'activo' => 'boolean',
        ]);

        $estructura = $this->normalizeEstructura($request->input('estructura', []));
        $this->validateEstructura($estructura);

        $template = FormTemplate::create([
            'nombre' => $validated['nombre'],
            'slug' => Str::slug($validated['nombre']),
            'estructura_json' => $estructura,
            'activo' => $validated['activo'] ?? true,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('forms.builder.index')
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function edit($template)
    {
        $template = FormTemplate::findOrFail($template);
        $template->load('creator');
        return view('forms.builder.edit', compact('template'));
    }

    public function update(Request $request, $template)
    {
        $template = FormTemplate::findOrFail($template);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'estructura' => 'required|array|min:1',
            'activo' => 'boolean',
        ]);

        $estructura = $this->normalizeEstructura($request->input('estructura', []));
        $this->validateEstructura($estructura);

        $template->update([
            'nombre' => $validated['nombre'],
            'estructura_json' => $estructura,
            'activo' => $validated['activo'] ?? true,
        ]);

        return redirect()
            ->route('forms.builder.index')
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function destroy($template)
    {
        $template = FormTemplate::findOrFail($template);

        // Verificar si tiene submissions
        $submissionsCount = $template->submissions()->count();
        if ($submissionsCount > 0) {
            return redirect()
                ->route('forms.builder.index')
                ->with('error', "No se puede eliminar la plantilla. Tiene {$submissionsCount} envíos asociados.");
        }

        $template->delete();

        return redirect()
            ->route('forms.builder.index')
            ->with('success', 'Plantilla eliminada correctamente.');
    }

    public function duplicate($template)
    {
        $template = FormTemplate::findOrFail($template);

        $newTemplate = $template->replicate();
        $newTemplate->nombre = $template->nombre . ' (Copia)';
        $newTemplate->slug = Str::slug($template->nombre . ' - ' . time());
        $newTemplate->activo = false; // Las copias se crean inactivas
        $newTemplate->created_by = auth()->id();
        $newTemplate->save();

        return redirect()->route('forms.builder.index')
            ->with('success', 'Plantilla duplicada correctamente.');
    }

    public function toggleActive($template)
    {
        $template = FormTemplate::findOrFail($template);

        $template->activo = !$template->activo;
        $template->save();

        $status = $template->activo ? 'activada' : 'desactivada';
        return redirect()->route('forms.builder.index')
            ->with('success', "Plantilla {$status} correctamente.");
    }

    private function normalizeEstructura(array $estructura): array
    {
        return array_values(array_map(function (array $campo): array {
            $tipo = (string) ($campo['tipo'] ?? '');

            $normalizado = [
                'nombre' => trim((string) ($campo['nombre'] ?? '')),
                'tipo' => $tipo,
                'requerido' => filter_var($campo['requerido'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];

            if ($tipo === 'select') {
                $opciones = array_values(array_filter(array_map(
                    static fn ($opcion) => trim((string) $opcion),
                    is_array($campo['opciones'] ?? null) ? $campo['opciones'] : []
                ), static fn (string $opcion) => $opcion !== ''));

                $normalizado['opciones'] = $opciones;
            }

            return $normalizado;
        }, $estructura));
    }

    private function validateEstructura(array $estructura): void
    {
        $validator = Validator::make([
            'estructura' => $estructura,
        ], [
            'estructura' => 'required|array|min:1',
            'estructura.*.nombre' => 'required|string|max:255',
            'estructura.*.tipo' => ['required', 'string', Rule::in(['text', 'number', 'textarea', 'select', 'checkbox'])],
            'estructura.*.requerido' => 'boolean',
            'estructura.*.opciones' => 'nullable|array',
            'estructura.*.opciones.*' => 'string|max:255',
        ]);

        $validator->after(function ($validator) use ($estructura) {
            foreach ($estructura as $index => $campo) {
                if (($campo['tipo'] ?? null) === 'select' && empty($campo['opciones'])) {
                    $validator->errors()->add("estructura.$index.opciones", 'Debe agregar al menos una opción para campos tipo selección.');
                }
            }
        });

        $validator->validate();
    }
}
