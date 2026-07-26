<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class GeneralNotification extends Notification
{
    public function __construct(
        public string $message,
        public string $type = 'info',
        public ?string $link = null,
        public ?int $senderId = null,
        public ?string $tenantId = null,
        public ?string $admin = null,

    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'link' => $this->link,
            'sender_id' => $this->senderId,
            'tenant_id' => $this->tenantId,
            'admin' => $this->admin,
        ];
    }
}
