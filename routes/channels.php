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
    // Allow all authenticated users to listen to their guardia's channel
    return $user !== null;
});
