<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormExecutionController extends Controller
{
    public function index()
    {
        $templates = FormTemplate::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $submissions = FormSubmission::with(['template', 'user'])
            ->delUsuario(auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('forms.execution.index', compact('templates', 'submissions'));
    }

    public function show(FormTemplate $template)
    {
        if (!$template->activo) {
            abort(404);
        }

        $template->load('creator');
        return view('forms.execution.show', compact('template'));
    }

    public function submit(Request $request, FormTemplate $template)
    {
        if (!$template->activo) {
            abort(404);
        }

        $rules = [];
        $messages = [];

        foreach ($template->estructura as $campo) {
            $fieldRules = [];

            if ($campo['requerido'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($campo['tipo']) {
                case 'text':
                    $fieldRules[] = 'string|max:255';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'textarea':
                    $fieldRules[] = 'string';
                    break;
                case 'select':
                    $fieldRules[] = 'string';
                    $fieldRules[] = Rule::in($campo['opciones'] ?? []);
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
            }

            $rules["campo_{$campo['nombre']}"] = $fieldRules;
            $messages["campo_{$campo['nombre']}.required"] = "El campo {$campo['nombre']} es obligatorio.";
            $messages["campo_{$campo['nombre']}.in"] = "El valor seleccionado para {$campo['nombre']} no es válido.";
        }

        $validated = $request->validate($rules, $messages);

        // Preparar datos para guardar
        $data = [];
        foreach ($template->estructura as $campo) {
            $fieldName = "campo_{$campo['nombre']}";
            $data[$campo['nombre']] = $validated[$fieldName] ?? null;
        }

        $submission = FormSubmission::create([
            'template_id' => $template->id,
            'user_id' => auth()->id(),
            'data_json' => $data,
            'estado' => 'enviado',
        ]);

        return redirect()
            ->route('forms.execution.index')
            ->with('success', 'Formulario enviado correctamente.');
    }

    public function draft(Request $request, FormTemplate $template)
    {
        if (!$template->activo) {
            abort(404);
        }

        // Misma validación que submit pero menos estricta
        $rules = [];
        foreach ($template->estructura as $campo) {
            $fieldRules = ['nullable'];

            switch ($campo['tipo']) {
                case 'text':
                    $fieldRules[] = 'string|max:255';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'textarea':
                    $fieldRules[] = 'string';
                    break;
                case 'select':
                    $fieldRules[] = 'string';
                    $fieldRules[] = Rule::in($campo['opciones'] ?? []);
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
            }

            $rules["campo_{$campo['nombre']}"] = $fieldRules;
        }

        $validated = $request->validate($rules);

        // Preparar datos para guardar
        $data = [];
        foreach ($template->estructura as $campo) {
            $fieldName = "campo_{$campo['nombre']}";
            $data[$campo['nombre']] = $validated[$fieldName] ?? null;
        }

        $submission = FormSubmission::updateOrCreate([
            'template_id' => $template->id,
            'user_id' => auth()->id(),
            'estado' => 'borrador',
        ], [
            'data_json' => $data,
        ]);

        return redirect()
            ->route('forms.execution.index')
            ->with('success', 'Borrador guardado correctamente.');
    }

    public function edit(FormSubmission $submission)
    {
        if ($submission->user_id !== auth()->id()) {
            abort(403);
        }

        $submission->load(['template', 'user']);
        return view('forms.execution.edit', compact('submission'));
    }

    public function update(Request $request, FormSubmission $submission)
    {
        if ($submission->user_id !== auth()->id()) {
            abort(403);
        }

        $template = $submission->template;
        $rules = [];
        $messages = [];

        foreach ($template->estructura as $campo) {
            $fieldRules = [];

            if ($campo['requerido'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($campo['tipo']) {
                case 'text':
                    $fieldRules[] = 'string|max:255';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'textarea':
                    $fieldRules[] = 'string';
                    break;
                case 'select':
                    $fieldRules[] = 'string';
                    $fieldRules[] = Rule::in($campo['opciones'] ?? []);
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
            }

            $rules["campo_{$campo['nombre']}"] = $fieldRules;
            $messages["campo_{$campo['nombre']}.required"] = "El campo {$campo['nombre']} es obligatorio.";
        }

        $validated = $request->validate($rules, $messages);

        // Preparar datos para guardar
        $data = [];
        foreach ($template->estructura as $campo) {
            $fieldName = "campo_{$campo['nombre']}";
            $data[$campo['nombre']] = $validated[$fieldName] ?? null;
        }

        $submission->update([
            'data_json' => $data,
            'estado' => 'enviado',
        ]);

        return redirect()
            ->route('forms.execution.index')
            ->with('success', 'Formulario actualizado correctamente.');
    }

    public function destroy(FormSubmission $submission)
    {
        if ($submission->user_id !== auth()->id()) {
            abort(403);
        }

        $submission->delete();

        return redirect()
            ->route('forms.execution.index')
            ->with('success', 'Registro eliminado correctamente.');
    }
}
