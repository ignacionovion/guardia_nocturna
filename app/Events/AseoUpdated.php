<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AseoUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $guardiaId;
    public $tenantId;
    public $assignedDate;
    public $assignments;

    public function __construct(int $guardiaId, string $assignedDate, array $assignments)
    {
        $this->guardiaId = $guardiaId;
        $this->tenantId = tenant('id');
        $this->assignedDate = $assignedDate;
        $this->assignments = $assignments;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("tenant.{$this->tenantId}.guardia.{$this->guardiaId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'aseo.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'guardia_id' => $this->guardiaId,
            'assigned_date' => $this->assignedDate,
            'assignments' => $this->assignments,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
