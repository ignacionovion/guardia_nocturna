<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantSettingsController extends Controller
{
    protected array $settingKeys = [
        'tenant_display_name',
        'tenant_logo',
        'tenant_timezone',
        'tenant_email_from',
        'tenant_email_name',
        'tenant_primary_color',
        'tenant_secondary_color',
    ];

    public function index()
    {
        $settings = [];
        foreach ($this->settingKeys as $key) {
            $settings[$key] = SystemSetting::getValue($key);
        }

        $tenant = tenant();

        return view('admin.tenant-settings', compact('settings', 'tenant'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'tenant_display_name' => ['nullable', 'string', 'max:255'],
            'tenant_timezone' => ['nullable', 'string', 'max:100'],
            'tenant_email_from' => ['nullable', 'email', 'max:255'],
            'tenant_email_name' => ['nullable', 'string', 'max:255'],
            'tenant_primary_color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'tenant_secondary_color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'tenant_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        // Save text settings
        foreach (['tenant_display_name', 'tenant_timezone', 'tenant_email_from', 'tenant_email_name', 'tenant_primary_color', 'tenant_secondary_color'] as $key) {
            if ($request->has($key)) {
                SystemSetting::setValue($key, $validated[$key] ?? null);
            }
        }

        // Handle logo upload
        if ($request->hasFile('tenant_logo')) {
            $oldLogo = SystemSetting::getValue('tenant_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('tenant_logo')->store('logos', 'public');
            SystemSetting::setValue('tenant_logo', $path);
        }

        // Remove logo
        if ($request->boolean('remove_logo')) {
            $oldLogo = SystemSetting::getValue('tenant_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            SystemSetting::setValue('tenant_logo', null);
        }

        return back()->with('success', 'Configuración actualizada correctamente.');
    }
}
