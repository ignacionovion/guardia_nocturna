<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            'estructura.*.nombre' => 'required|string|max:255',
            'estructura.*.tipo' => ['required', 'string', Rule::in(['text', 'number', 'textarea', 'select', 'checkbox'])],
            'estructura.*.requerido' => 'boolean',
            'estructura.*.opciones' => 'required_if:estructura.*.tipo,select|array',
            'estructura.*.opciones.*' => 'string|max:255',
            'activo' => 'boolean',
        ]);

        $template = FormTemplate::create([
            'nombre' => $validated['nombre'],
            'slug' => Str::slug($validated['nombre']),
            'estructura_json' => $validated['estructura'],
            'activo' => $validated['activo'] ?? true,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('forms.builder.index')
            ->with('success', 'Plantilla creada correctamente.');
    }

    public function edit(FormTemplate $template)
    {
        $template->load('creator');
        return view('forms.builder.edit', compact('template'));
    }

    public function update(Request $request, FormTemplate $template)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'estructura' => 'required|array|min:1',
            'estructura.*.nombre' => 'required|string|max:255',
            'estructura.*.tipo' => ['required', 'string', Rule::in(['text', 'number', 'textarea', 'select', 'checkbox'])],
            'estructura.*.requerido' => 'boolean',
            'estructura.*.opciones' => 'required_if:estructura.*.tipo,select|array',
            'estructura.*.opciones.*' => 'string|max:255',
            'activo' => 'boolean',
        ]);

        $template->update([
            'nombre' => $validated['nombre'],
            'estructura_json' => $validated['estructura'],
            'activo' => $validated['activo'] ?? true,
        ]);

        return redirect()
            ->route('forms.builder.index')
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    public function destroy(FormTemplate $template)
    {
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

    public function duplicate(FormTemplate $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->nombre = $template->nombre . ' (Copia)';
        $newTemplate->slug = Str::slug($template->nombre . ' - ' . time());
        $newTemplate->activo = false; // Las copias se crean inactivas
        $newTemplate->created_by = auth()->id();
        $newTemplate->save();

        return redirect()->route('forms.builder.index')
            ->with('success', 'Plantilla duplicada correctamente.');
    }

    public function toggleActive(FormTemplate $template)
    {
        $template->activo = !$template->activo;
        $template->save();

        $status = $template->activo ? 'activada' : 'desactivada';
        return redirect()->route('forms.builder.index')
            ->with('success', "Plantilla {$status} correctamente.");
    }
}
