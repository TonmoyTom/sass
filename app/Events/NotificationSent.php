<?php

namespace App\Events;

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $userId;

    public $type;

    public $link;

    public function __construct($message, $userId, $type = 'info', $link = null)
    {
        $this->message = $message;
        $this->userId = $userId;
        $this->type = $type;
        $this->link = $link;
        $user = User::find($userId);
        $user?->notify(new GeneralNotification($message, $type, $link));
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.'.$this->userId);
    }

    public function broadcastAs()
    {
        return 'notification.sent';
    }

    public function broadcastWith()
    {
        $user = User::find($this->userId);
        $latestNotification = $user?->notifications()->latest()->first();

        return [
            'id' => $latestNotification?->id,
            'message' => $this->message,
            'type' => $this->type,
            'timestamp' => now()->toDateTimeString(),
            'userName' => $user?->name ?? 'System',
            'action' => $this->message,
            'project' => '',
            'userImage' => $user?->avatar_url ?? '/images/user/default.jpg',
            'link' => $this->link,
        ];
    }
}
