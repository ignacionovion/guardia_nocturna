<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BrandingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrandingController extends Controller
{
    private BrandingService $brandingService;

    public function __construct(BrandingService $brandingService)
    {
        $this->brandingService = $brandingService;
    }

    /**
     * Show branding configuration form
     */
    public function index()
    {
        // Check if addon is enabled
        if (!addon('custom_branding')) {
            return view('admin.branding.disabled');
        }

        $branding = $this->brandingService->getBrandingForEdit();
        $defaults = [
            'nombre_empresa' => 'GuardiAPP',
            'color_primario' => '#f59e0b',
            'color_secundario' => '#1e293b',
            'color_sidebar' => '#0f172a',
        ];

        return view('admin.branding.index', [
            'branding' => $branding,
            'defaults' => $defaults,
        ]);
    }

    /**
     * Save branding configuration
     */
    public function store(Request $request)
    {
        // Check if addon is enabled
        if (!addon('custom_branding')) {
            return redirect()
                ->route('admin.branding.index')
                ->with('error', 'Tu plan actual no incluye Marca Personalizada.');
        }

        $validated = $request->validate([
            'nombre_empresa' => ['nullable', 'string', 'max:100'],
            'color_primario' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_secundario' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'color_sidebar' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $this->brandingService->saveBranding($validated);

        return redirect()
            ->route('admin.branding.index')
            ->with('success', 'Configuración de marca actualizada correctamente.');
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        if (!addon('custom_branding')) {
            return redirect()
                ->route('admin.branding.index')
                ->with('error', 'Tu plan actual no incluye Marca Personalizada.');
        }

        $request->validate([
            'logo' => ['required', 'image', 'max:2048'], // Max 2MB
        ]);

        $tenantId = tenant('id');
        $file = $request->file('logo');
        
        // Normalizar extensión (solo usar la extensión real del mime type)
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
            $extension = 'png'; // fallback seguro
        }
        
        $filename = 'logo.' . $extension;

        // Delete old logo if exists
        Storage::disk('public')->deleteDirectory('branding/' . $tenantId);

        // Store new logo with sanitized name
        $path = $file->storeAs('branding/' . $tenantId, $filename, 'public');

        $this->brandingService->updateLogo($path);

        return redirect()
            ->route('admin.branding.index')
            ->with('success', 'Logo actualizado correctamente.');
    }

    /**
     * Upload favicon
     */
    public function uploadFavicon(Request $request)
    {
        if (!addon('custom_branding')) {
            return redirect()
                ->route('admin.branding.index')
                ->with('error', 'Tu plan actual no incluye Marca Personalizada.');
        }

        $request->validate([
            'favicon' => ['required', 'image', 'max:1024', 'mimes:png,ico'], // Max 1MB, only PNG or ICO
        ]);

        $tenantId = tenant('id');
        $file = $request->file('favicon');
        
        // Normalizar extensión
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['png', 'ico'])) {
            $extension = 'png';
        }
        
        $filename = 'favicon.' . $extension;

        // Store new favicon
        $path = $file->storeAs('branding/' . $tenantId, $filename, 'public');

        $this->brandingService->updateFavicon($path);

        return redirect()
            ->route('admin.branding.index')
            ->with('success', 'Favicon actualizado correctamente.');
    }

    /**
     * Remove logo
     */
    public function removeLogo()
    {
        if (!addon('custom_branding')) {
            return redirect()
                ->route('admin.branding.index')
                ->with('error', 'Tu plan actual no incluye Marca Personalizada.');
        }

        $this->brandingService->removeLogo();

        return redirect()
            ->route('admin.branding.index')
            ->with('success', 'Logo eliminado. Se usará el logo por defecto.');
    }

    /**
     * Remove favicon
     */
    public function removeFavicon()
    {
        if (!addon('custom_branding')) {
            return redirect()
                ->route('admin.branding.index')
                ->with('error', 'Tu plan actual no incluye Marca Personalizada.');
        }

        $this->brandingService->removeFavicon();

        return redirect()
            ->route('admin.branding.index')
            ->with('success', 'Favicon eliminado. Se usará el favicon por defecto.');
    }
}
