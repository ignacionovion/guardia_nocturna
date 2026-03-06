<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    /**
     * Create a new event instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'created_at' => $this->notification->created_at->toISOString(),
            'user' => $this->notification->user ? [
                'id' => $this->notification->user->id,
                'name' => $this->notification->user->name,
            ] : null,
            'firefighter' => $this->notification->firefighter ? [
                'id' => $this->notification->firefighter->id,
                'nombres' => $this->notification->firefighter->nombres,
                'apellido_paterno' => $this->notification->firefighter->apellido_paterno,
            ] : null,
            'guardia' => $this->notification->guardia ? [
                'id' => $this->notification->guardia->id,
                'name' => $this->notification->guardia->name,
            ] : null,
            'metadata' => $this->notification->metadata,
        ];
    }
}
