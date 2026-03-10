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
