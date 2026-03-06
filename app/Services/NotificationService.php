<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    private const SUPER_ADMIN_ROLES = ['super_admin', 'capitania'];

    /**
     * Send notification to super admin and capitan roles
     */
    public static function notify(string $type, string $title, ?string $message = null, array $context = []): void
    {
        $metadata = [
            'timestamp' => now()->toIso8601String(),
            'context' => $context,
        ];

        $notification = Notification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'user_id' => $context['user_id'] ?? null,
            'firefighter_id' => $context['firefighter_id'] ?? null,
            'guardia_id' => $context['guardia_id'] ?? null,
            'metadata' => $metadata,
        ]);

        // Broadcast the notification in real-time
        broadcast(new NotificationCreated($notification))->toOthers();
    }

    public static function attendanceSaved(User $user, Guardia $guardia, int $confirmedCount): void
    {
        self::notify(
            'attendance_saved',
            "Asistencia guardada - {$guardia->name}",
            "{$confirmedCount} bomberos confirmados en la guardia",
            [
                'user_id' => $user->id,
                'guardia_id' => $guardia->id,
                'confirmed_count' => $confirmedCount,
            ]
        );
    }

    public static function replacementCreated(User $user, Bombero $original, Bombero $replacement, Guardia $guardia): void
    {
        self::notify(
            'replacement',
            "Reemplazo efectuado - {$guardia->name}",
            "{$replacement->nombres} {$replacement->apellido_paterno} reemplaza a {$original->nombres} {$original->apellido_paterno}",
            [
                'user_id' => $user->id,
                'guardia_id' => $guardia->id,
                'original_firefighter_id' => $original->id,
                'replacement_firefighter_id' => $replacement->id,
            ]
        );
    }

    public static function refuerzoCreated(User $user, Bombero $firefighter, Guardia $guardia): void
    {
        self::notify(
            'refuerzo',
            "Refuerzo agregado - {$guardia->name}",
            "{$firefighter->nombres} {$firefighter->apellido_paterno} agregado como refuerzo",
            [
                'user_id' => $user->id,
                'guardia_id' => $guardia->id,
                'firefighter_id' => $firefighter->id,
            ]
        );
    }

    public static function noveltyCreated(User $user, ?Bombero $firefighter, string $noveltyType, ?Guardia $guardia = null): void
    {
        $title = $firefighter 
            ? "Novedad registrada - {$firefighter->nombres} {$firefighter->apellido_paterno}"
            : "Novedad registrada - Sin bombero asignado";
            
        self::notify(
            'novelty',
            $title,
            "Tipo: {$noveltyType}",
            [
                'user_id' => $user->id,
                'guardia_id' => $guardia?->id,
                'firefighter_id' => $firefighter?->id,
                'novelty_type' => $noveltyType,
            ]
        );
    }

    public static function bedAssigned(User $user, Bombero $firefighter, int $bedNumber, ?Guardia $guardia = null, string $source = 'dashboard'): void
    {
        self::notify(
            'bed_assigned',
            "Cama asignada - {$firefighter->nombres} {$firefighter->apellido_paterno}",
            "Cama #{$bedNumber} asignada vía {$source}",
            [
                'user_id' => $user?->id,
                'guardia_id' => $guardia?->id,
                'firefighter_id' => $firefighter->id,
                'bed_number' => $bedNumber,
                'source' => $source,
            ]
        );
    }

    public static function emergencyCreated(User $user, string $emergencyType, string $location, ?Guardia $guardia = null): void
    {
        self::notify(
            'emergency',
            "EMERGENCIA - {$emergencyType}",
            "Ubicación: {$location}",
            [
                'user_id' => $user->id,
                'guardia_id' => $guardia?->id,
                'emergency_type' => $emergencyType,
                'location' => $location,
            ]
        );
    }

    public static function statusChanged(User $user, Bombero $firefighter, string $oldStatus, string $newStatus, ?Guardia $guardia = null): void
    {
        self::notify(
            'status_changed',
            "Estado actualizado - {$firefighter->nombres} {$firefighter->apellido_paterno}",
            "Cambió de '{$oldStatus}' a '{$newStatus}'",
            [
                'user_id' => $user->id,
                'guardia_id' => $guardia?->id,
                'firefighter_id' => $firefighter->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]
        );
    }

    public static function inventoryMovement(User $user, string $itemName, string $movementType, int $quantity, ?Guardia $guardia = null): void
    {
        $action = $movementType === 'ingreso' ? 'Ingresó' : 'Egresó';
        self::notify(
            'inventory_movement',
            "{$action} inventario - {$itemName}",
            "{$quantity} unidades {$movementType}",
            [
                'user_id' => $user->id,
                'guardia_id' => $guardia?->id,
                'item_name' => $itemName,
                'movement_type' => $movementType,
                'quantity' => $quantity,
            ]
        );
    }

    public static function formularioCompleted(User $user, string $formType, string $formName, ?Guardia $guardia = null): void
    {
        self::notify(
            'form_completed',
            "Formulario completado - {$formName}",
            "Tipo: {$formType}",
            [
                'user_id' => $user->id,
                'guardia_id' => $guardia?->id,
                'form_type' => $formType,
                'form_name' => $formName,
            ]
        );
    }

    public static function preventiveCreated(User $user, string $preventiveTitle, ?Guardia $guardia = null): void
    {
        self::notify(
            'preventive',
            "Preventiva creada - {$preventiveTitle}",
            'Nueva preventiva registrada en el sistema',
            [
                'user_id' => $user->id,
                'guardia_id' => $guardia?->id,
                'preventive_title' => $preventiveTitle,
            ]
        );
    }
}
