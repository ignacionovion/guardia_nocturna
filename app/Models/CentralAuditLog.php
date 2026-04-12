<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Central audit log for tracking all admin actions in the SaaS panel.
 *
 * Actions: tenant_created, tenant_updated, tenant_deleted, tenant_suspended,
 *          features_updated, migrations_run, seed_run, backup_run, plan_changed, etc.
 */
class CentralAuditLog extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'admin_id',
        'admin_name',
        'tenant_id',
        'action',
        'description',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Log an action to the audit trail.
     */
    public static function log(
        string $action,
        string $description,
        ?string $tenantId = null,
        ?array $metadata = null,
    ): self {
        $admin = Auth::guard('central')->user();

        return static::create([
            'admin_id' => $admin?->id,
            'admin_name' => $admin?->name ?? $admin?->email ?? 'system',
            'tenant_id' => $tenantId,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Scope: filter by tenant.
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: filter by action type.
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get the action icon for display.
     */
    public function actionIcon(): string
    {
        return match($this->action) {
            'tenant_created'    => '🟢',
            'tenant_updated'    => '✏️',
            'tenant_deleted'    => '🔴',
            'tenant_suspended'  => '⏸️',
            'tenant_reactivated'=> '▶️',
            'plan_changed'      => '📋',
            'features_updated'  => '🔧',
            'migrations_run'    => '🗄️',
            'seed_run'          => '🌱',
            'tenant_captain_initial_access' => '🔑',
            'tenant_captain_password_reset' => '🔁',
            'backup_run'        => '💾',
            'backup_restored'   => '♻️',
            'estado_changed'    => '🔄',
            default             => '📝',
        };
    }
}
