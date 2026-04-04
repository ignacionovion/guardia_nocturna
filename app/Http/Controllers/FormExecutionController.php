<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

    public function show(Request $request)
    {
        $templateId = (int) $request->route('template');

        $template = FormTemplate::query()->findOrFail($templateId);

        if (!$template->activo) {
            abort(403, 'Formulario no disponible');
        }

        $template->load('creator');

        return view('forms.execution.show', compact('template'));
    }

    public function submit(Request $request)
    {
        $templateId = (int) $request->route('template');

        $template = FormTemplate::query()->findOrFail($templateId);

        if (!$template->activo) {
            abort(403, 'Formulario no disponible');
        }

        $rules = [];
        $messages = [];

        foreach ($template->estructura as $index => $campo) {
            $fieldName = "campo_{$index}";
            $fieldRules = [];

            if ($campo['requerido'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($campo['tipo']) {
                case 'text':
                    $fieldRules = array_merge($fieldRules, ['string', 'max:255']);
                    break;
                case 'number':
                    $fieldRules = array_merge($fieldRules, ['numeric']);
                    break;
                case 'textarea':
                    $fieldRules = array_merge($fieldRules, ['string']);
                    break;
                case 'select':
                    $fieldRules = array_merge($fieldRules, ['string', Rule::in($campo['opciones'] ?? [])]);
                    break;
                case 'checkbox':
                    $fieldRules = array_merge($fieldRules, ['boolean']);
                    break;
            }

            $rules[$fieldName] = $fieldRules;
            $messages["{$fieldName}.required"] = "El campo {$campo['nombre']} es obligatorio.";
            $messages["{$fieldName}.in"] = "El valor seleccionado para {$campo['nombre']} no es válido.";
        }

        $validated = $request->validate($rules, $messages);

        // Preparar datos para guardar
        $data = [];
        foreach ($template->estructura as $index => $campo) {
            $fieldName = "campo_{$index}";
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
            ->with('success', 'Formulario enviado correctamente. Ya no puede ser modificado.');
    }

    public function draft(Request $request)
    {
        $templateId = (int) $request->route('template');

        $template = FormTemplate::query()->findOrFail($templateId);

        if (!$template->activo) {
            abort(403, 'Formulario no disponible');
        }

        // Misma validación que submit pero menos estricta
        $rules = [];
        foreach ($template->estructura as $index => $campo) {
            $fieldName = "campo_{$index}";
            $fieldRules = ['nullable'];

            switch ($campo['tipo']) {
                case 'text':
                    $fieldRules = array_merge($fieldRules, ['string', 'max:255']);
                    break;
                case 'number':
                    $fieldRules = array_merge($fieldRules, ['numeric']);
                    break;
                case 'textarea':
                    $fieldRules = array_merge($fieldRules, ['string']);
                    break;
                case 'select':
                    $fieldRules = array_merge($fieldRules, ['string', Rule::in($campo['opciones'] ?? [])]);
                    break;
                case 'checkbox':
                    $fieldRules = array_merge($fieldRules, ['boolean']);
                    break;
            }

            $rules[$fieldName] = $fieldRules;
        }

        $validated = $request->validate($rules);

        // Preparar datos para guardar
        $data = [];
        foreach ($template->estructura as $index => $campo) {
            $fieldName = "campo_{$index}";
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

    public function edit(Request $request)
    {
        $submissionParam = $request->route('submission');

        if (!is_numeric($submissionParam) || (int) $submissionParam <= 0) {
            abort(404, 'Submission no válido');
        }

        $submissionId = (int) $submissionParam;

        $submission = FormSubmission::query()->findOrFail($submissionId);

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

    public function update(Request $request)
    {
        $submissionParam = $request->route('submission');

        if (!is_numeric($submissionParam) || (int) $submissionParam <= 0) {
            abort(404, 'Submission no válido');
        }

        $submissionId = (int) $submissionParam;

        $submission = FormSubmission::query()->findOrFail($submissionId);

        if ($submission->user_id !== auth()->id()) {
            abort(403);
        }

        // Solo se pueden editar borradores
        if ($submission->estado !== 'borrador') {
            return redirect()->route('forms.execution.index')
                ->with('error', 'Solo se pueden editar borradores. Este formulario ya fue enviado.');
        }

        $template = $submission->template;
        $rules = [];
        $messages = [];

        foreach ($template->estructura as $index => $campo) {
            $fieldName = "campo_{$index}";
            $fieldRules = [];

            // En update() de borradores, todos los campos son nullable
            $fieldRules[] = 'nullable';

            switch ($campo['tipo']) {
                case 'text':
                    $fieldRules = array_merge($fieldRules, ['string', 'max:255']);
                    break;
                case 'number':
                    $fieldRules = array_merge($fieldRules, ['numeric']);
                    break;
                case 'textarea':
                    $fieldRules = array_merge($fieldRules, ['string']);
                    break;
                case 'select':
                    $fieldRules = array_merge($fieldRules, ['string', Rule::in($campo['opciones'] ?? [])]);
                    break;
                case 'checkbox':
                    $fieldRules = array_merge($fieldRules, ['boolean']);
                    break;
            }

            $rules[$fieldName] = $fieldRules;
        }

        $validated = $request->validate($rules);

        $data = [];
        foreach ($template->estructura as $index => $campo) {
            $fieldName = "campo_{$index}";
            $data[$campo['nombre']] = $validated[$fieldName] ?? null;
        }

        $submission->update([
            'data_json' => $data,
        ]);

        return redirect()
            ->route('forms.execution.index')
            ->with('success', 'Borrador actualizado correctamente.');
    }

    public function destroy(Request $request)
    {
        $submissionParam = $request->route('submission');

        if (!is_numeric($submissionParam) || (int) $submissionParam <= 0) {
            abort(404, 'Submission no válido');
        }

        $submissionId = (int) $submissionParam;

        $submission = FormSubmission::query()->findOrFail($submissionId);

        if ($submission->user_id !== auth()->id()) {
            abort(403);
        }

        $submission->delete();

        return redirect()
            ->route('forms.execution.index')
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function finalize(Request $request)
    {
        $submissionParam = $request->route('submission');

        if (!is_numeric($submissionParam) || (int) $submissionParam <= 0) {
            abort(404, 'Submission no válido');
        }

        $submissionId = (int) $submissionParam;

        $submission = FormSubmission::query()->findOrFail($submissionId);

        if ($submission->user_id !== auth()->id()) {
            abort(403);
        }

        // Solo se pueden finalizar borradores
        if ($submission->estado !== 'borrador') {
            return redirect()->route('forms.execution.index')
                ->with('error', 'Este formulario ya fue enviado.');
        }

        $template = $submission->template;
        $rules = [];
        $messages = [];

        foreach ($template->estructura as $index => $campo) {
            $fieldName = "campo_{$index}";
            $fieldRules = [];

            if ($campo['requerido'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($campo['tipo']) {
                case 'text':
                    $fieldRules = array_merge($fieldRules, ['string', 'max:255']);
                    break;
                case 'number':
                    $fieldRules = array_merge($fieldRules, ['numeric']);
                    break;
                case 'textarea':
                    $fieldRules = array_merge($fieldRules, ['string']);
                    break;
                case 'select':
                    $fieldRules = array_merge($fieldRules, ['string', Rule::in($campo['opciones'] ?? [])]);
                    break;
                case 'checkbox':
                    $fieldRules = array_merge($fieldRules, ['boolean']);
                    break;
            }

            $rules[$fieldName] = $fieldRules;
            $messages["{$fieldName}.required"] = "El campo {$campo['nombre']} es obligatorio.";
            $messages["{$fieldName}.in"] = "El valor seleccionado para {$campo['nombre']} no es válido.";
        }

        $validated = $request->validate($rules, $messages);

        // Preparar datos para guardar
        $data = [];
        foreach ($template->estructura as $index => $campo) {
            $fieldName = "campo_{$index}";
            $data[$campo['nombre']] = $validated[$fieldName] ?? null;
        }

        $submission->update([
            'data_json' => $data,
            'estado' => 'enviado',
        ]);

        return redirect()
            ->route('forms.execution.index')
            ->with('success', 'Formulario enviado correctamente. Ya no puede ser modificado.');
    }

    public function showQr()
    {
        // Generar URL multi-tenant automáticamente
        $qrUrl = route('qr.forms.validate');
        
        return view('forms.execution.qr', compact('qrUrl'));
    }

    public function downloadQr()
    {
        // Generar URL multi-tenant automáticamente
        $qrUrl = route('qr.forms.validate');
        
        // Generar QR como PNG
        $qr = QrCode::format('png')
            ->size(500)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($qrUrl);
        
        return response($qr)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="formularios-qr-v2.png"');
    }
}
