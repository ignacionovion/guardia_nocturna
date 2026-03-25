<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    /**
     * Display a listing of plans
     */
    public function index()
    {
        $plans = Plan::orderBy('orden')->orderBy('id')->get();
        
        return view('central.billing.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan
     */
    public function create()
    {
        $availableModules = Plan::availableModules();
        $availableAddons = Plan::availableAddons();
        
        return view('central.billing.plans.create', compact('availableModules', 'availableAddons'));
    }

    /**
     * Store a newly created plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:50', 'unique:plans,slug', 'regex:/^[a-z0-9\-]+$/'],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio_mensual' => ['required', 'numeric', 'min:0'],
            'precio_anual' => ['required', 'numeric', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_guardias' => ['nullable', 'integer', 'min:1'],
            'max_beds' => ['nullable', 'integer', 'min:1'],
            'max_storage_mb' => ['nullable', 'integer', 'min:1'],
            'orden' => ['required', 'integer', 'min:0'],
            'activo' => ['boolean'],
        ]);

        // Procesar features (módulos)
        $features = [];
        foreach (Plan::availableModules() as $key => $label) {
            $features[$key] = $request->has("feature_{$key}");
        }

        // Procesar addons
        $addons = [];
        foreach (Plan::availableAddons() as $key => $label) {
            $addons[$key] = $request->has("addon_{$key}");
        }

        $validated['features'] = $features;
        $validated['addons'] = $addons;
        $validated['activo'] = $request->has('activo');

        Plan::create($validated);

        return redirect()
            ->route('central.billing.plans.index')
            ->with('success', 'Plan creado exitosamente.');
    }

    /**
     * Show the form for editing the specified plan
     */
    public function edit(Plan $plan)
    {
        $availableModules = Plan::availableModules();
        $availableAddons = Plan::availableAddons();
        
        return view('central.billing.plans.edit', compact('plan', 'availableModules', 'availableAddons'));
    }

    /**
     * Update the specified plan
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:50', Rule::unique('plans')->ignore($plan->id), 'regex:/^[a-z0-9\-]+$/'],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'precio_mensual' => ['required', 'numeric', 'min:0'],
            'precio_anual' => ['required', 'numeric', 'min:0'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_guardias' => ['nullable', 'integer', 'min:1'],
            'max_beds' => ['nullable', 'integer', 'min:1'],
            'max_storage_mb' => ['nullable', 'integer', 'min:1'],
            'orden' => ['required', 'integer', 'min:0'],
            'activo' => ['boolean'],
        ]);

        // Procesar features (módulos)
        $features = [];
        foreach (Plan::availableModules() as $key => $label) {
            $features[$key] = $request->has("feature_{$key}");
        }

        // Procesar addons
        $addons = [];
        foreach (Plan::availableAddons() as $key => $label) {
            $addons[$key] = $request->has("addon_{$key}");
        }

        $validated['features'] = $features;
        $validated['addons'] = $addons;
        $validated['activo'] = $request->has('activo');

        $plan->update($validated);

        return redirect()
            ->route('central.billing.plans.index')
            ->with('success', 'Plan actualizado exitosamente.');
    }

    /**
     * Remove the specified plan
     */
    public function destroy(Plan $plan)
    {
        // Verificar si el plan está en uso
        if ($plan->tenants()->count() > 0) {
            return redirect()
                ->route('central.billing.plans.index')
                ->with('error', "No se puede eliminar el plan '{$plan->nombre}' porque está asignado a {$plan->tenants()->count()} tenant(s). Desactívalo en su lugar.");
        }

        $plan->delete();

        return redirect()
            ->route('central.billing.plans.index')
            ->with('success', 'Plan eliminado exitosamente.');
    }

    /**
     * Toggle plan active status
     */
    public function toggle(Plan $plan)
    {
        $plan->update(['activo' => !$plan->activo]);

        $estado = $plan->activo ? 'activado' : 'desactivado';

        return redirect()
            ->route('central.billing.plans.index')
            ->with('success', "Plan {$estado} exitosamente.");
    }
}
