<?php

namespace App\Observers;

use App\Models\BedAssignment;

class BedAssignmentObserver
{
    /**
     * Handle the BedAssignment "created" event.
     * Cuando se crea una asignación activa, la cama pasa a occupied.
     */
    public function created(BedAssignment $assignment): void
    {
        if ($assignment->bed && is_null($assignment->ended_at)) {
            $assignment->bed->update(['status' => 'occupied']);
        }
    }

    /**
     * Handle the BedAssignment "updated" event.
     * Cuando una asignación se cierra (ended_at deja de ser null), la cama vuelve a available.
     */
    public function updated(BedAssignment $assignment): void
    {
        // Solo actuar si ended_at cambió de null a una fecha (asignación cerrada)
        if ($assignment->wasChanged('ended_at') && !is_null($assignment->ended_at)) {
            if ($assignment->bed) {
                $assignment->bed->update(['status' => 'available']);
            }
        }
    }
}
