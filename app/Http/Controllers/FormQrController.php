<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\FormSubmission;
use App\Models\Bombero;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class FormQrController extends Controller
{
    /**
     * Pantalla 1: Validación de RUT
     */
    public function validateRut()
    {
        return view('qr.forms.validate-rut');
    }

    /**
     * Procesar validación de RUT
     */
    public function processRut(Request $request)
    {
        $request->validate([
            'rut' => 'required|string|max:12',
        ]);

        $rut = $this->cleanRut($request->rut);

        // Buscar en bomberos
        $bombero = Bombero::where('rut', $rut)->first();

        if (!$bombero) {
            return back()->with('error', 'RUT no encontrado en el sistema. Contacta a tu capitán.');
        }

        // Guardar en sesión temporal
        Session::put('qr_form_rut', $rut);
        Session::put('qr_form_bombero_id', $bombero->id);
        Session::put('qr_form_bombero_name', trim($bombero->nombres . ' ' . $bombero->apellido_paterno));

        return redirect()->route('qr.forms.list');
    }

    /**
     * Pantalla 2: Listado de formularios activos
     */
    public function listForms()
    {
        if (!Session::has('qr_form_rut')) {
            return redirect()->route('qr.forms.validate');
        }

        $templates = FormTemplate::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $bomberoId = Session::get('qr_form_bombero_id');
        $bomberoName = Session::get('qr_form_bombero_name');

        // Obtener borradores del bombero
        $drafts = FormSubmission::where('bombero_id', $bomberoId)
            ->where('estado', 'borrador')
            ->pluck('template_id')
            ->toArray();

        return view('qr.forms.list', compact('templates', 'bomberoName', 'drafts'));
    }

    /**
     * Pantalla 3: Ejecución del formulario
     */
    public function showForm($templateId)
    {
        if (!Session::has('qr_form_rut')) {
            return redirect()->route('qr.forms.validate');
        }

        $template = FormTemplate::findOrFail($templateId);

        if (!$template->activo) {
            abort(403, 'Formulario no disponible');
        }

        $bomberoId = Session::get('qr_form_bombero_id');
        $bomberoName = Session::get('qr_form_bombero_name');

        // Buscar borrador existente
        $draft = FormSubmission::where('template_id', $template->id)
            ->where('bombero_id', $bomberoId)
            ->where('estado', 'borrador')
            ->first();

        return view('qr.forms.show', compact('template', 'bomberoName', 'draft'));
    }

    /**
     * Guardar borrador
     */
    public function saveDraft(Request $request, $templateId)
    {
        if (!Session::has('qr_form_rut')) {
            return redirect()->route('qr.forms.validate');
        }

        $template = FormTemplate::findOrFail($templateId);

        if (!$template->activo) {
            abort(403, 'Formulario no disponible');
        }

        $bomberoId = Session::get('qr_form_bombero_id');

        // Validación flexible para borradores
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

        // Preparar datos
        $data = [];
        foreach ($template->estructura as $index => $campo) {
            $fieldName = "campo_{$index}";
            $data[$campo['nombre']] = $validated[$fieldName] ?? null;
        }

        // Actualizar o crear borrador
        FormSubmission::updateOrCreate([
            'template_id' => $template->id,
            'bombero_id' => $bomberoId,
            'estado' => 'borrador',
        ], [
            'data_json' => $data,
            'user_id' => null, // QR no tiene user_id
        ]);

        return redirect()
            ->route('qr.forms.list')
            ->with('success', 'Borrador guardado correctamente. Puedes continuar después.');
    }

    /**
     * Enviar formulario final
     */
    public function submitForm(Request $request, $templateId)
    {
        if (!Session::has('qr_form_rut')) {
            return redirect()->route('qr.forms.validate');
        }

        $template = FormTemplate::findOrFail($templateId);

        if (!$template->activo) {
            abort(403, 'Formulario no disponible');
        }

        $bomberoId = Session::get('qr_form_bombero_id');

        // Validación estricta para envío final
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

        // Preparar datos
        $data = [];
        foreach ($template->estructura as $index => $campo) {
            $fieldName = "campo_{$index}";
            $data[$campo['nombre']] = $validated[$fieldName] ?? null;
        }

        // Buscar borrador existente para actualizarlo o crear nuevo
        $submission = FormSubmission::where('template_id', $template->id)
            ->where('bombero_id', $bomberoId)
            ->where('estado', 'borrador')
            ->first();

        if ($submission) {
            $submission->update([
                'data_json' => $data,
                'estado' => 'enviado',
            ]);
        } else {
            FormSubmission::create([
                'template_id' => $template->id,
                'bombero_id' => $bomberoId,
                'user_id' => null,
                'data_json' => $data,
                'estado' => 'enviado',
            ]);
        }

        return redirect()
            ->route('qr.forms.success')
            ->with('success', 'Formulario enviado correctamente.');
    }

    /**
     * Pantalla de éxito
     */
    public function success()
    {
        if (!Session::has('qr_form_rut')) {
            return redirect()->route('qr.forms.validate');
        }

        $bomberoName = Session::get('qr_form_bombero_name');

        return view('qr.forms.success', compact('bomberoName'));
    }

    /**
     * Cerrar sesión QR
     */
    public function logout()
    {
        Session::forget('qr_form_rut');
        Session::forget('qr_form_bombero_id');
        Session::forget('qr_form_bombero_name');

        return redirect()->route('qr.forms.validate')->with('success', 'Sesión cerrada correctamente.');
    }

    /**
     * Limpiar RUT
     */
    private function cleanRut($rut)
    {
        return strtoupper(str_replace(['.', '-', ' '], '', $rut));
    }
}
