<?php

namespace App\Events;

use App\Models\Bombero;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefuerzoRemoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bomberoId;
    public $guardiaId;
    public $tenantId;
    public $firefighterName;

    public function __construct(int $bomberoId, int $guardiaId, string $firefighterName)
    {
        $this->bomberoId = $bomberoId;
        $this->guardiaId = $guardiaId;
        $this->firefighterName = $firefighterName;
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
        return 'refuerzo.removed';
    }

    public function broadcastWith(): array
    {
        return [
            'bombero_id' => $this->bomberoId,
            'firefighter_name' => $this->firefighterName,
            'guardia_id' => $this->guardiaId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
