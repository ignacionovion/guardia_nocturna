<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReplacementUndone implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $replacementId;
    public $guardiaId;
    public $tenantId;
    public $originalName;
    public $replacementName;

    public function __construct(int $replacementId, int $guardiaId, string $originalName, string $replacementName)
    {
        $this->replacementId = $replacementId;
        $this->guardiaId = $guardiaId;
        $this->originalName = $originalName;
        $this->replacementName = $replacementName;
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
        return 'replacement.undone';
    }

    public function broadcastWith(): array
    {
        return [
            'replacement_id' => $this->replacementId,
            'original_name' => $this->originalName,
            'replacement_name' => $this->replacementName,
            'guardia_id' => $this->guardiaId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
