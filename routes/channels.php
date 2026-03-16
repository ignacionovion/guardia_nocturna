<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado para notificaciones — aislado por tenant
Broadcast::channel('tenant.{tenantId}.notifications', function ($user, $tenantId) {
    $currentTenant = tenant();
    if (!$currentTenant || $currentTenant->id !== $tenantId) {
        return false;
    }
    return in_array($user->role, ['super_admin', 'capitania']);
});

// Canal público para guardia live — aislado por tenant
Broadcast::channel('tenant.{tenantId}.guardia.{guardiaId}', function ($user, $tenantId, $guardiaId) {
    $currentTenant = tenant();
    if (!$currentTenant || $currentTenant->id !== $tenantId) {
        return false;
    }
    
    // Super admin and capitania can access all guardias
    if (in_array($user->role, ['super_admin', 'capitania'], true)) {
        return true;
    }
    
    // Guardia role users can only access their own guardia
    if ($user->role === 'guardia' && $user->guardia_id) {
        return (int) $user->guardia_id === (int) $guardiaId;
    }
    
    // Allow authenticated users to view (read-only access)
    return $user !== null;
});
