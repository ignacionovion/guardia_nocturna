<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\View\View;

final class FinancialDashboardController extends Controller
{
    public function index(): View
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $metricsRaw = Payment::query()
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN status = 'paid' AND paid_at BETWEEN ? AND ? THEN amount ELSE 0 END), 0) as monthly_income,
                 COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as total_income,
                 COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) as pending_payments_count",
                [$startOfMonth->toDateString(), $endOfMonth->toDateString()]
            )
            ->first();

        $tenantStateCounts = Tenant::query()
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END), 0) as active_count,
                 COALESCE(SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END), 0) as expired_count,
                 COALESCE(SUM(CASE WHEN estado = ? THEN 1 ELSE 0 END), 0) as suspended_count",
                [Tenant::ESTADO_ACTIVO, Tenant::ESTADO_VENCIDO, Tenant::ESTADO_SUSPENDIDO]
            )
            ->first();

        $recentPayments = Payment::query()
            ->with(['tenant:id,nombre'])
            ->orderByDesc('id')
            ->limit(20)
            ->get([
                'id',
                'tenant_id',
                'amount',
                'currency',
                'payment_method',
                'status',
                'paid_at',
                'reference',
            ]);

        $upcomingExpirations = Tenant::query()
            ->with('planRelation:id,nombre')
            ->whereIn('estado', [Tenant::ESTADO_ACTIVO, Tenant::ESTADO_TRIAL])
            ->whereNotNull('fecha_vencimiento')
            ->whereBetween('fecha_vencimiento', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
            ->orderBy('fecha_vencimiento')
            ->limit(20)
            ->get(['id', 'nombre', 'plan_id', 'estado', 'fecha_vencimiento', 'grace_days'])
            ->map(function (Tenant $tenant): Tenant {
                $tenant->days_remaining = now()->startOfDay()->diffInDays($tenant->fecha_vencimiento->copy()->startOfDay(), false);
                return $tenant;
            });

        $expiredTenants = Tenant::query()
            ->with('planRelation:id,nombre')
            ->where('estado', Tenant::ESTADO_VENCIDO)
            ->whereNotNull('fecha_vencimiento')
            ->orderBy('fecha_vencimiento')
            ->limit(30)
            ->get(['id', 'nombre', 'plan_id', 'estado', 'fecha_vencimiento', 'grace_days']);

        $riskTenants = $expiredTenants
            ->map(function (Tenant $tenant): Tenant {
                $daysOverdue = max(0, now()->startOfDay()->diffInDays($tenant->fecha_vencimiento->copy()->startOfDay(), false) * -1);
                $tenant->days_overdue = $daysOverdue;
                $tenant->in_grace = $tenant->isInGracePeriod();
                return $tenant;
            })
            ->sortByDesc('days_overdue')
            ->values();

        $paymentMethodSummary = Payment::query()
            ->where('status', Payment::STATUS_PAID)
            ->selectRaw(
                "CASE
                    WHEN LOWER(payment_method) = 'transferencia' THEN 'transferencia'
                    WHEN LOWER(payment_method) = 'efectivo' THEN 'efectivo'
                    WHEN LOWER(payment_method) = 'webpay' THEN 'webpay'
                    ELSE 'otros'
                 END as method_group,
                 COUNT(*) as payments_count,
                 COALESCE(SUM(amount), 0) as total_amount"
            )
            ->groupBy('method_group')
            ->orderByRaw("FIELD(method_group, 'transferencia', 'efectivo', 'webpay', 'otros')")
            ->get();

        $mailTypes = collect(config('mail_strategy.types', []));
        $activeMailTypes = $mailTypes->filter(fn (array $data): bool => (bool) ($data['enabled'] ?? false))->keys()->values();
        $disabledMailTypes = $mailTypes->filter(fn (array $data): bool => ! ((bool) ($data['enabled'] ?? false)))->keys()->values();

        return view('central.financial.index', [
            'metrics' => [
                'monthly_income' => (float) ($metricsRaw?->monthly_income ?? 0),
                'total_income' => (float) ($metricsRaw?->total_income ?? 0),
                'pending_payments_count' => (int) ($metricsRaw?->pending_payments_count ?? 0),
                'active_tenants_count' => (int) ($tenantStateCounts?->active_count ?? 0),
                'expired_tenants_count' => (int) ($tenantStateCounts?->expired_count ?? 0),
                'suspended_tenants_count' => (int) ($tenantStateCounts?->suspended_count ?? 0),
            ],
            'recentPayments' => $recentPayments,
            'upcomingExpirations' => $upcomingExpirations,
            'riskTenants' => $riskTenants,
            'paymentMethodSummary' => $paymentMethodSummary,
            'activeMailTypes' => $activeMailTypes,
            'disabledMailTypes' => $disabledMailTypes,
            'generatedAt' => Carbon::now(),
        ]);
    }
}
