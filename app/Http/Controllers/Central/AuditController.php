<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = CentralAuditLog::query()->latest();

        // Filter by tenant
        if ($request->filled('tenant_id')) {
            $query->forTenant($request->tenant_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->ofAction($request->action);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to . ' 23:59:59');
        }

        $logs = $query->paginate(30)->withQueryString();
        $tenants = Tenant::orderBy('nombre')->get(['id', 'nombre']);

        $actions = [
            'tenant_created' => 'Tenant Creado',
            'tenant_updated' => 'Tenant Actualizado',
            'tenant_deleted' => 'Tenant Eliminado',
            'plan_changed' => 'Plan Cambiado',
            'estado_changed' => 'Estado Cambiado',
            'features_updated' => 'Features Actualizados',
            'migrations_run' => 'Migraciones',
            'seed_run' => 'Seeders',
            'backup_run' => 'Backup',
            'backup_restored' => 'Restore',
        ];

        return view('central.audit.index', compact('logs', 'tenants', 'actions'));
    }
}
