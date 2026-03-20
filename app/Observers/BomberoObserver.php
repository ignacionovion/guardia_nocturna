<?php

namespace App\Observers;

use App\Models\Bombero;
use App\Models\BedAssignment;
use App\Models\MapaBomberoUsuarioLegacy;

class BomberoObserver
{
    /**
     * Handle the Bombero "updated" event.
     * Libera automáticamente la cama cuando el bombero deja de estar presente.
     */
    public function updated(Bombero $bombero)
    {
        // Estados que requieren liberación de cama
        $nonPresentStatuses = ['ausente', 'permiso', 'licencia', 'falta', 'inhabilitado'];
        
        // Si el bombero cambió a un estado no presente o está fuera de servicio
        if (in_array($bombero->estado_asistencia, $nonPresentStatuses) || $bombero->fuera_de_servicio) {
            // Obtener user_id asociado al bombero
            $userId = MapaBomberoUsuarioLegacy::where('firefighter_id', $bombero->id)->value('user_id');
            
            if ($userId) {
                // Buscar asignación activa
                $activeAssignment = BedAssignment::where('volunteer_id', $userId)
                    ->whereNull('ended_at')
                    ->first();
                
                if ($activeAssignment) {
                    // Liberar cama automáticamente
                    $activeAssignment->update([
                        'ended_at' => now(),
                        'notes' => ($activeAssignment->notes ? $activeAssignment->notes . ' | ' : '') 
                                  . 'Liberada automáticamente: cambio de estado a ' . $bombero->estado_asistencia,
                    ]);
                    
                    // Actualizar estado de la cama
                    $bed = $activeAssignment->bed;
                    if ($bed) {
                        $bed->update(['status' => 'available']);
                    }
                    
                    \Log::info("Cama liberada automáticamente para bombero {$bombero->id} (user {$userId}) - Estado: {$bombero->estado_asistencia}");
                }
            }
        }
    }
}
