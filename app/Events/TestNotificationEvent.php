<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $userId;

    public function __construct($userId, $message = 'Test notification!')
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        if (function_exists('tenant') && tenant()) {
            return [
                new PrivateChannel('tenant.'.tenant('id').'.user.'.$this->userId),
            ];
        }

        return [
            new PrivateChannel('central.user.'.$this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => uniqid(),
            'message' => $this->message,
            'timestamp' => now()->toIso8601String(),
            'link' => '#',
        ];
    }
}
