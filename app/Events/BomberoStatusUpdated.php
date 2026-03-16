<?php

namespace App\Events;

use App\Models\Bombero;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BomberoStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bombero;
    public $guardiaId;
    public $tenantId;

    public function __construct(Bombero $bombero, int $guardiaId)
    {
        $this->bombero = $bombero;
        $this->guardiaId = $guardiaId;
        $this->tenantId = tenant('id');
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("tenant.{$this->tenantId}.guardia.{$this->guardiaId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'bombero.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'bombero_id' => $this->bombero->id,
            'estado_asistencia' => $this->bombero->estado_asistencia,
            'nombres' => $this->bombero->nombres,
            'apellido_paterno' => $this->bombero->apellido_paterno,
            'guardia_id' => $this->bombero->guardia_id,
            'es_refuerzo' => $this->bombero->es_refuerzo,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
