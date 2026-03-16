<?php

namespace App\Events;

use App\Models\Bombero;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BomberoConfirmed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bomberoId;
    public $guardiaId;
    public $tenantId;
    public $confirmedAt;

    public function __construct(int $bomberoId, int $guardiaId)
    {
        $this->bomberoId = $bomberoId;
        $this->guardiaId = $guardiaId;
        $this->tenantId = tenant('id');
        $this->confirmedAt = now();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("tenant.{$this->tenantId}.guardia.{$this->guardiaId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'bombero.confirmed';
    }

    public function broadcastWith(): array
    {
        return [
            'bombero_id' => $this->bomberoId,
            'confirmed_at' => $this->confirmedAt->toIso8601String(),
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
