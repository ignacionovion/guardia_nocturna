<?php

namespace App\Events;

use App\Models\Emergency;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergenciaCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $emergency;
    public $guardiaId;
    public $tenantId;

    public function __construct(Emergency $emergency, int $guardiaId)
    {
        $this->emergency = $emergency;
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
        return 'emergencia.created';
    }

    public function broadcastWith(): array
    {
        return [
            'emergency_id' => $this->emergency->id,
            'emergency_key_id' => $this->emergency->emergency_key_id,
            'dispatched_at' => $this->emergency->dispatched_at?->toIso8601String(),
            'guardia_id' => $this->guardiaId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
