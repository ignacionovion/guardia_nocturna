<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a tenant user.
     */
    public function start(Request $request, $tenant)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer'],
        ]);

        $centralAdmin = Auth::guard('central')->user();

        // Get the user from tenant database
        $user = null;
        $tenant->run(function () use ($validated, &$user) {
            $user = \App\Models\User::find($validated['user_id']);
        });

        if (!$user) {
            return back()->with('error', 'Usuario no encontrado en el tenant.');
        }

        // Store impersonation data in session
        Session::put('impersonating', [
            'central_admin_id' => $centralAdmin->id,
            'central_admin_name' => $centralAdmin->name,
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->nombre,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'started_at' => now()->toIso8601String(),
        ]);

        // Log the impersonation
        CentralAuditLog::log('impersonation_start', "Impersonación iniciada: {$user->name} en «{$tenant->nombre}»", $tenant->id, [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);

        // Build the tenant URL
        $domain = $tenant->domains->first()?->domain;
        if (!$domain) {
            return back()->with('error', 'El tenant no tiene dominio configurado.');
        }

        $centralDomain = env('CENTRAL_DOMAIN', 'localhost');
        $tenantUrl = "http://{$domain}.{$centralDomain}/impersonate/callback?token=" . $this->generateImpersonationToken($tenant->id, $user->id);

        return redirect($tenantUrl);
    }

    /**
     * Stop impersonating and return to central panel.
     */
    public function stop(Request $request)
    {
        $impersonation = Session::get('impersonating');

        if ($impersonation) {
            CentralAuditLog::log('impersonation_stop', "Impersonación finalizada: {$impersonation['user_name']} en «{$impersonation['tenant_name']}»", $impersonation['tenant_id'], [
                'duration_minutes' => now()->diffInMinutes($impersonation['started_at']),
            ]);

            Session::forget('impersonating');
        }

        // Logout from tenant
        Auth::guard('web')->logout();

        $centralDomain = env('CENTRAL_DOMAIN', 'localhost');
        return redirect("http://{$centralDomain}/admin");
    }

    /**
     * Generate a secure token for impersonation callback.
     */
    protected function generateImpersonationToken(string $tenantId, int $userId): string
    {
        $data = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'expires' => now()->addMinutes(5)->timestamp,
        ];

        return base64_encode(encrypt(json_encode($data)));
    }

    /**
     * Validate and decode impersonation token.
     */
    public static function validateToken(string $token): ?array
    {
        try {
            $data = json_decode(decrypt(base64_decode($token)), true);

            if (!$data || $data['expires'] < now()->timestamp) {
                return null;
            }

            return $data;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
