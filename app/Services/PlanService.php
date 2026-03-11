<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class PlanService
{
    // Verificar si el tenant actual tiene una feature
    public static function hasFeature(string $feature): bool
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return false;
        }
        
        // Si el tenant tiene plan_id, usar el plan de la tabla
        if ($tenant->plan_id && $tenant->plan) {
            return $tenant->plan->hasFeature($feature);
        }
        
        // Fallback: usar el campo plan string (legacy) con defaults
        return self::getLegacyFeature($tenant->plan ?? 'basico', $feature);
    }
    
    // Obtener límite para el tenant actual
    public static function getLimit(string $type): ?int
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return null;
        }
        
        // Si tiene plan_id en la tabla
        if ($tenant->plan_id && $tenant->plan) {
            return $tenant->plan->getLimit($type);
        }
        
        // Fallback: legacy
        return self::getLegacyLimit($tenant->plan ?? 'basico', $type);
    }
    
    // Verificar si está cerca del límite (80% o más)
    public static function isNearLimit(string $type): bool
    {
        $limit = self::getLimit($type);
        
        if ($limit === null) {
            return false; // Ilimitado
        }
        
        $current = self::getCurrentUsage($type);
        $threshold = (int) ($limit * 0.8);
        
        return $current >= $threshold;
    }
    
    // Verificar si excede el límite
    public static function exceedsLimit(string $type, int $additional = 0): bool
    {
        $limit = self::getLimit($type);
        
        if ($limit === null) {
            return false; // Ilimitado
        }
        
        $current = self::getCurrentUsage($type);
        
        return ($current + $additional) > $limit;
    }
    
    // Obtener uso actual según el tipo
    public static function getCurrentUsage(string $type): int
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return 0;
        }
        
        return $tenant->run(function () use ($type) {
            return match($type) {
                'users' => \App\Models\User::count(),
                'guardias' => \App\Models\User::where('role', 'guardia')->count(),
                'beds' => \App\Models\Bed::count(),
                'storage' => self::calculateStorageUsage(),
                default => 0,
            };
        });
    }
    
    // Calcular uso de storage en MB
    protected static function calculateStorageUsage(): int
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return 0;
        }
        
        $path = storage_path("app/tenants/{$tenant->id}");
        
        if (!is_dir($path)) {
            return 0;
        }
        
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return (int) ($size / 1024 / 1024); // Convertir a MB
    }
    
    // Obtener información completa de uso para UI
    public static function getUsageInfo(): array
    {
        $types = ['users', 'guardias', 'beds', 'storage'];
        $info = [];
        
        foreach ($types as $type) {
            $limit = self::getLimit($type);
            $current = self::getCurrentUsage($type);
            
            $info[$type] = [
                'limit' => $limit,
                'current' => $current,
                'remaining' => $limit !== null ? max(0, $limit - $current) : null,
                'percentage' => $limit !== null ? round(($current / $limit) * 100, 1) : null,
                'unlimited' => $limit === null,
                'near_limit' => self::isNearLimit($type),
                'exceeded' => $limit !== null && $current > $limit,
            ];
        }
        
        return $info;
    }
    
    // Features para planes legacy (cuando no hay plan_id)
    protected static function getLegacyFeature(string $plan, string $feature): bool
    {
        $features = [
            'basico' => [
                'reportes_avanzados' => false,
                'whatsapp_integration' => false,
                'estadisticas_avanzadas' => false,
                'backup_automatico' => false,
                'api_access' => false,
                'custom_branding' => false,
                'priority_support' => false,
                'audit_logs' => false,
                'multi_body' => false,
                'advanced_notifications' => false,
            ],
            'profesional' => [
                'reportes_avanzados' => true,
                'whatsapp_integration' => true,
                'estadisticas_avanzadas' => true,
                'backup_automatico' => true,
                'api_access' => false,
                'custom_branding' => false,
                'priority_support' => false,
                'audit_logs' => true,
                'multi_body' => false,
                'advanced_notifications' => true,
            ],
            'enterprise' => [
                'reportes_avanzados' => true,
                'whatsapp_integration' => true,
                'estadisticas_avanzadas' => true,
                'backup_automatico' => true,
                'api_access' => true,
                'custom_branding' => true,
                'priority_support' => true,
                'audit_logs' => true,
                'multi_body' => true,
                'advanced_notifications' => true,
            ],
        ];
        
        return $features[$plan][$feature] ?? false;
    }
    
    // Límites para planes legacy
    protected static function getLegacyLimit(string $plan, string $type): ?int
    {
        $limits = [
            'basico' => [
                'users' => 5,
                'guardias' => 20,
                'beds' => 10,
                'storage' => 100,
            ],
            'profesional' => [
                'users' => 15,
                'guardias' => 50,
                'beds' => 30,
                'storage' => 500,
            ],
            'enterprise' => [
                'users' => null,
                'guardias' => null,
                'beds' => null,
                'storage' => 5000,
            ],
        ];
        
        return $limits[$plan][$type] ?? null;
    }
    
    // Cambiar plan de un tenant (usado desde panel central)
    public static function changePlan(Tenant $tenant, int $planId): bool
    {
        $plan = Plan::find($planId);
        
        if (!$plan || !$plan->activo) {
            return false;
        }
        
        $tenant->update([
            'plan_id' => $plan->id,
            'plan' => $plan->slug, // mantener sincronizado
        ]);
        
        return true;
    }
    
    // Obtener plan actual del tenant
    public static function getCurrentPlan(): ?Plan
    {
        $tenant = tenant();
        
        if (!$tenant) {
            return null;
        }
        
        if ($tenant->plan_id && $tenant->plan) {
            return $tenant->plan;
        }
        
        // Buscar por slug legacy
        return Plan::where('slug', $tenant->plan ?? 'basico')->first();
    }
}
