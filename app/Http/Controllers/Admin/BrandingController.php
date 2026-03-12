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
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        $tenantId = tenant('id');
        $file = $request->file('logo');
        
        // Verificar que el archivo es válido
        if (!$file || !$file->isValid()) {
            return redirect()
                ->route('admin.branding.index')
                ->with('error', 'Error: El archivo no es válido o no se subió correctamente.');
        }

        try {
            // Normalizar extensión
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                $extension = 'png';
            }
            
            $directory = 'branding/' . $tenantId;
            $filename = 'logo.' . $extension;

            // Asegurar que el directorio exista
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Borrar logo anterior específico (no todo el directorio)
            $oldBranding = $this->brandingService->getBrandingForEdit();
            if ($oldBranding && $oldBranding->logo_path) {
                Storage::disk('public')->delete($oldBranding->logo_path);
            }

            // Guardar archivo usando putFileAs para más control
            $path = Storage::disk('public')->putFileAs(
                $directory,
                $file,
                $filename
            );

            if (!$path) {
                throw new \Exception('No se pudo guardar el archivo.');
            }

            // Guardar ruta relativa en BD
            $this->brandingService->updateLogo($path);

            return redirect()
                ->route('admin.branding.index')
                ->with('success', 'Logo actualizado correctamente. Ruta: ' . $path);

        } catch (\Exception $e) {
            \Log::error('Error subiendo logo: ' . $e->getMessage());
            return redirect()
                ->route('admin.branding.index')
                ->with('error', 'Error al guardar el logo: ' . $e->getMessage());
        }
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
            'favicon' => ['required', 'image', 'max:1024', 'mimes:png,ico'],
        ]);

        $tenantId = tenant('id');
        $file = $request->file('favicon');
        
        if (!$file || !$file->isValid()) {
            return redirect()
                ->route('admin.branding.index')
                ->with('error', 'Error: El archivo no es válido.');
        }

        try {
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, ['png', 'ico'])) {
                $extension = 'png';
            }
            
            $directory = 'branding/' . $tenantId;
            $filename = 'favicon.' . $extension;

            // Asegurar que el directorio exista
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Borrar favicon anterior
            $oldBranding = $this->brandingService->getBrandingForEdit();
            if ($oldBranding && $oldBranding->favicon_path) {
                Storage::disk('public')->delete($oldBranding->favicon_path);
            }

            // Guardar archivo
            $path = Storage::disk('public')->putFileAs(
                $directory,
                $file,
                $filename
            );

            if (!$path) {
                throw new \Exception('No se pudo guardar el favicon.');
            }

            $this->brandingService->updateFavicon($path);

            return redirect()
                ->route('admin.branding.index')
                ->with('success', 'Favicon actualizado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error subiendo favicon: ' . $e->getMessage());
            return redirect()
                ->route('admin.branding.index')
                ->with('error', 'Error al guardar el favicon: ' . $e->getMessage());
        }
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
