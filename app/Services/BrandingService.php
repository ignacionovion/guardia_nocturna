<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BrandingService
{
    /**
     * Default branding values
     */
    private array $defaults = [
        'logo' => null,
        'favicon' => null,
        'nombre_empresa' => 'GuardiAPP',
        'color_primario' => '#f59e0b',      // amber-500
        'color_secundario' => '#1e293b',    // slate-800
        'color_sidebar' => '#0f172a',      // slate-900
    ];

    /**
     * Get branding for current tenant
     *
     * @return object Branding data with fallbacks to defaults
     */
    public function getBranding(): object
    {
        $tenantId = tenant('id');

        if (!$tenantId) {
            return $this->toObject($this->defaults);
        }

        try {
            $branding = DB::table('tenant_branding')
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$branding) {
                return $this->toObject($this->defaults);
            }

            return $this->toObject([
                'logo' => $branding->logo_path ? Storage::disk('public')->url($branding->logo_path) : $this->defaults['logo'],
                'favicon' => $branding->favicon_path ? Storage::disk('public')->url($branding->favicon_path) : $this->defaults['favicon'],
                'nombre_empresa' => $branding->nombre_empresa ?? $this->defaults['nombre_empresa'],
                'color_primario' => $branding->color_primario ?? $this->defaults['color_primario'],
                'color_secundario' => $branding->color_secundario ?? $this->defaults['color_secundario'],
                'color_sidebar' => $branding->color_sidebar ?? $this->defaults['color_sidebar'],
            ]);
        } catch (\Exception $e) {
            // Table doesn't exist yet - return defaults
            return $this->toObject($this->defaults);
        }
    }

    /**
     * Get raw branding data for editing (without URL conversion)
     */
    public function getBrandingForEdit(): ?object
    {
        $tenantId = tenant('id');

        if (!$tenantId) {
            return null;
        }

        return DB::table('tenant_branding')
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Save or update branding for current tenant
     */
    public function saveBranding(array $data): bool
    {
        $tenantId = tenant('id');

        if (!$tenantId) {
            return false;
        }

        $existing = DB::table('tenant_branding')
            ->where('tenant_id', $tenantId)
            ->first();

        $payload = [
            'nombre_empresa' => $data['nombre_empresa'] ?? null,
            'color_primario' => $data['color_primario'] ?? null,
            'color_secundario' => $data['color_secundario'] ?? null,
            'color_sidebar' => $data['color_sidebar'] ?? null,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('tenant_branding')
                ->where('tenant_id', $tenantId)
                ->update($payload);
        } else {
            $payload['tenant_id'] = $tenantId;
            $payload['created_at'] = now();
            DB::table('tenant_branding')->insert($payload);
        }

        return true;
    }

    /**
     * Update logo path for current tenant
     */
    public function updateLogo(string $path): bool
    {
        return $this->updateFileField('logo_path', $path);
    }

    /**
     * Update favicon path for current tenant
     */
    public function updateFavicon(string $path): bool
    {
        return $this->updateFileField('favicon_path', $path);
    }

    /**
     * Remove logo for current tenant
     */
    public function removeLogo(): bool
    {
        $tenantId = tenant('id');

        if (!$tenantId) {
            return false;
        }

        $branding = DB::table('tenant_branding')
            ->where('tenant_id', $tenantId)
            ->first();

        if ($branding && $branding->logo_path) {
            Storage::delete($branding->logo_path);
            DB::table('tenant_branding')
                ->where('tenant_id', $tenantId)
                ->update(['logo_path' => null, 'updated_at' => now()]);
        }

        return true;
    }

    /**
     * Remove favicon for current tenant
     */
    public function removeFavicon(): bool
    {
        $tenantId = tenant('id');

        if (!$tenantId) {
            return false;
        }

        $branding = DB::table('tenant_branding')
            ->where('tenant_id', $tenantId)
            ->first();

        if ($branding && $branding->favicon_path) {
            Storage::delete($branding->favicon_path);
            DB::table('tenant_branding')
                ->where('tenant_id', $tenantId)
                ->update(['favicon_path' => null, 'updated_at' => now()]);
        }

        return true;
    }

    /**
     * Update a file field (logo or favicon)
     */
    private function updateFileField(string $field, string $path): bool
    {
        $tenantId = tenant('id');

        if (!$tenantId) {
            return false;
        }

        $existing = DB::table('tenant_branding')
            ->where('tenant_id', $tenantId)
            ->first();

        // Delete old file if exists
        if ($existing && $existing->{$field}) {
            Storage::delete($existing->{$field});
        }

        if ($existing) {
            DB::table('tenant_branding')
                ->where('tenant_id', $tenantId)
                ->update([
                    $field => $path,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('tenant_branding')->insert([
                'tenant_id' => $tenantId,
                $field => $path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * Convert array to object
     */
    private function toObject(array $data): object
    {
        return (object) $data;
    }
}
