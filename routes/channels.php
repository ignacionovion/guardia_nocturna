<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado para notificaciones - solo super admin y capitan
Broadcast::channel('notifications', function ($user) {
    return in_array($user->role, ['super_admin', 'capitania']);
});
