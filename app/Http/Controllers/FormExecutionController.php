<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function show($template)
    {
        $templateId = (int) $template;

        $templateModel = FormTemplate::query()->find($templateId);

        if (!$templateModel) {
            dd([
                'error' => 'template_not_found',
                'requested_id' => $templateId,
                'existing_ids' => FormTemplate::query()->pluck('id')->all(),
                'all_templates' => FormTemplate::query()->get(['id', 'nombre', 'activo'])->toArray(),
            ]);
        }

        if (!$templateModel->activo) {
            abort(403, 'Formulario no disponible');
        }

        $templateModel->load('creator');

        return view('forms.execution.show', ['template' => $templateModel]);
    }

    public function submit(Request $request, $template)
    {
        $templateId = (int) $template;

        $templateModel = FormTemplate::query()->find($templateId);

        if (!$templateModel) {
            dd([
                'error' => 'template_not_found',
                'requested_id' => $templateId,
                'existing_ids' => FormTemplate::query()->pluck('id')->all(),
                'all_templates' => FormTemplate::query()->get(['id', 'nombre', 'activo'])->toArray(),
            ]);
        }

        if (!$templateModel->activo) {
            abort(403, 'Formulario no disponible');
        }

        $rules = [];
        $messages = [];

        foreach ($templateModel->estructura as $campo) {
            $fieldName = "campo_{$campo['nombre']}";
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
        foreach ($templateModel->estructura as $campo) {
            $fieldName = "campo_{$campo['nombre']}";
            $data[$campo['nombre']] = $validated[$fieldName] ?? null;
        }

        $submission = FormSubmission::create([
            'template_id' => $templateModel->id,
            'user_id' => auth()->id(),
            'data_json' => $data,
            'estado' => 'enviado',
        ]);

        return redirect()
            ->route('forms.execution.index')
            ->with('success', 'Formulario enviado correctamente. Ya no puede ser modificado.');
    }

    public function draft(Request $request, $template)
    {
        $templateId = (int) $template;

        $templateModel = FormTemplate::query()->find($templateId);

        if (!$templateModel) {
            dd([
                'error' => 'template_not_found',
                'requested_id' => $templateId,
                'existing_ids' => FormTemplate::query()->pluck('id')->all(),
                'all_templates' => FormTemplate::query()->get(['id', 'nombre', 'activo'])->toArray(),
            ]);
        }

        if (!$templateModel->activo) {
            abort(403, 'Formulario no disponible');
        }

        // Misma validación que submit pero menos estricta
        $rules = [];
        foreach ($templateModel->estructura as $campo) {
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
        foreach ($templateModel->estructura as $campo) {
            $fieldName = "campo_{$campo['nombre']}";
            $data[$campo['nombre']] = $validated[$fieldName] ?? null;
        }

        $submission = FormSubmission::updateOrCreate([
            'template_id' => $templateModel->id,
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
        // Solo se pueden editar borradores
        if ($submission->estado !== 'borrador') {
            return redirect()->route('forms.execution.index')
                ->with('error', 'Solo se pueden editar borradores. Este formulario ya fue enviado.');
        }

        // Solo el dueño puede editar
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
            }

            $rules[$fieldName] = $fieldRules;
        }

        $validated = $request->validate($rules);

        $data = [];
        foreach ($template->estructura as $campo) {
            $fieldName = "campo_{$campo['nombre']}";
            $data[$campo['nombre']] = $validated[$fieldName] ?? null;
        }

        $submission->update([
            'data_json' => $data,
        ]);

        return redirect()
            ->route('forms.execution.index')
            ->with('success', 'Borrador actualizado correctamente.');
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
