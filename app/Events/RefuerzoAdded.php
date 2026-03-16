<?php

namespace App\Events;

use App\Models\Bombero;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefuerzoAdded implements ShouldBroadcast
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
        return 'refuerzo.added';
    }

    public function broadcastWith(): array
    {
        return [
            'bombero_id' => $this->bombero->id,
            'nombres' => $this->bombero->nombres,
            'apellido_paterno' => $this->bombero->apellido_paterno,
            'apellido_materno' => $this->bombero->apellido_materno,
            'guardia_id' => $this->guardiaId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
