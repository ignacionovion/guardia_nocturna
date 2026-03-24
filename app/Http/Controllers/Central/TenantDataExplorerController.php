<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\CentralAuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantDataExplorerController extends Controller
{
    /**
     * List all tables in tenant database.
     */
    public function index($tenant)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        $tables = [];
        try {
            $tenant->run(function () use (&$tables) {
                $tables = DB::select('SHOW TABLES');
                $tables = array_map(function ($table) {
                    $values = array_values((array) $table);
                    return $values[0];
                }, $tables);
            });
        } catch (\Throwable $e) {
            Log::error("Failed to list tables for tenant {$tenant->id}: {$e->getMessage()}");
        }

        return view('central.tenants.explorer.index', compact('tenant', 'tables'));
    }

    /**
     * View records from a specific table.
     */
    public function table(Request $request, $tenant, string $table)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        $search = $request->get('search');
        $perPage = $request->get('per_page', 25);

        $records = [];
        $columns = [];
        $total = 0;

        try {
            $tenant->run(function () use ($table, $search, $perPage, &$records, &$columns, &$total, $request) {
                // Get column names
                $columnsInfo = DB::select("SHOW COLUMNS FROM `{$table}`");
                $columns = array_map(fn($col) => $col->Field, $columnsInfo);

                // Build query
                $query = DB::table($table);

                // Search across all columns
                if ($search) {
                    $query->where(function ($q) use ($columns, $search) {
                        foreach ($columns as $column) {
                            $q->orWhere($column, 'LIKE', "%{$search}%");
                        }
                    });
                }

                $total = $query->count();
                $records = $query->orderBy($columns[0] ?? 'id', 'desc')
                    ->paginate($perPage)
                    ->withQueryString();
            });
        } catch (\Throwable $e) {
            Log::error("Failed to query table {$table} for tenant {$tenant->id}: {$e->getMessage()}");
            return back()->with('error', 'Error al consultar la tabla: ' . $e->getMessage());
        }

        return view('central.tenants.explorer.table', compact('tenant', 'table', 'columns', 'records', 'search', 'total'));
    }

    /**
     * View a single record.
     */
    public function showRecord(Request $request, $tenant, string $table, string $id)
    {
        // Handle route model binding manually for central routes
        if (!$tenant instanceof Tenant) {
            $tenant = Tenant::findOrFail($tenant);
        }

        $record = null;
        $columns = [];

        try {
            $tenant->run(function () use ($table, $id, &$record, &$columns) {
                $columnsInfo = DB::select("SHOW COLUMNS FROM `{$table}`");
                $columns = array_map(fn($col) => $col->Field, $columnsInfo);

                // Try to find by primary key or first column
                $primaryKey = collect($columnsInfo)->first(fn($col) => $col->Key === 'PRI')?->Field ?? $columns[0];

                $record = DB::table($table)->where($primaryKey, $id)->first();
            });
        } catch (\Throwable $e) {
            Log::error("Failed to get record from {$table} for tenant {$tenant->id}: {$e->getMessage()}");
        }

        if (!$record) {
            return back()->with('error', 'Registro no encontrado.');
        }

        return view('central.tenants.explorer.record', compact('tenant', 'table', 'record', 'columns'));
    }
}
