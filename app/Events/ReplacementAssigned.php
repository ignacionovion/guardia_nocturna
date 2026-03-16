<?php

namespace App\Events;

use App\Models\Bombero;
use App\Models\ReemplazoBombero;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReplacementAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $replacement;
    public $guardiaId;
    public $tenantId;

    public function __construct(ReemplazoBombero $replacement, int $guardiaId)
    {
        $this->replacement = $replacement;
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
        return 'replacement.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'replacement_id' => $this->replacement->id,
            'original_firefighter_id' => $this->replacement->original_firefighter_id,
            'replacement_firefighter_id' => $this->replacement->replacement_firefighter_id,
            'guardia_id' => $this->guardiaId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
