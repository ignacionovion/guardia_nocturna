<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Body;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['body', 'domains'])->latest()->paginate(20);
        return view('central.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $bodies = Body::where('activo', true)->orderBy('nombre')->get();
        return view('central.tenants.form', ['tenant' => null, 'bodies' => $bodies]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/', 'unique:tenants,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'numero' => ['nullable', 'integer', 'min:1'],
            'body_id' => ['nullable', 'exists:bodies,id'],
            'plan' => ['required', 'in:basico,profesional,enterprise'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'seed' => ['boolean'],
        ]);

        $tenant = Tenant::create([
            'id' => $validated['id'],
            'nombre' => $validated['nombre'],
            'numero' => $validated['numero'] ?? null,
            'body_id' => $validated['body_id'] ?? null,
            'plan' => $validated['plan'],
            'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
        ]);

        // Subdomain = tenant id
        $tenant->domains()->create(['domain' => $validated['id']]);

        if ($request->boolean('seed')) {
            $tenant->run(function () {
                $seeder = new \Database\Seeders\DatabaseSeeder();
                $seeder->run();
            });
        }

        return redirect()->route('central.tenants.index')
            ->with('success', "Compañía «{$tenant->nombre}» creada. DB: {$tenant->tenancy_db_name}");
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['body', 'domains']);
        return view('central.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        $bodies = Body::where('activo', true)->orderBy('nombre')->get();
        return view('central.tenants.form', compact('tenant', 'bodies'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'numero' => ['nullable', 'integer', 'min:1'],
            'body_id' => ['nullable', 'exists:bodies,id'],
            'plan' => ['required', 'in:basico,profesional,enterprise'],
            'activo' => ['boolean'],
            'fecha_vencimiento' => ['nullable', 'date'],
        ]);

        $validated['activo'] = $request->boolean('activo', true);
        $tenant->update($validated);

        return redirect()->route('central.tenants.index')
            ->with('success', "Compañía «{$tenant->nombre}» actualizada.");
    }

    public function destroy(Tenant $tenant)
    {
        $nombre = $tenant->nombre;
        $tenant->delete();

        return redirect()->route('central.tenants.index')
            ->with('success', "Compañía «{$nombre}» eliminada junto con su base de datos.");
    }
}
