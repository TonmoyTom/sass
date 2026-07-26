<?php

namespace App\Events;

use App\Models\TenantUser;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $userId;

    public $type;

    public $link;

    public $senderId;

    public $tenantId;

    public $admin;

    public function __construct($message, $userId, $type = 'info', $link = null, $senderId = null, $tenantId = null, $admin = null)
    {
        $this->message = $message;
        $this->userId = $userId;
        $this->type = $type;
        $this->link = $link;
        $this->senderId = $senderId;
        $this->tenantId = $tenantId ?? (function_exists('tenant') && tenant() ? tenant('id') : null);
        $this->admin = $admin;

        Log::info('[NotificationSent] Constructing event', [
            'userId' => $this->userId,
            'tenantId' => $this->tenantId,
            'tenant_helper_active' => function_exists('tenant') && tenant() ? tenant('id') : 'NOT ACTIVE',
            'db_connection' => DB::connection()->getDatabaseName(),
            'admin' => $this->admin,
        ]);

        $user = $this->resolveUser();

        // Log::info('[NotificationSent] User lookup result', [
        //     'userId' => $userId,
        //     'model_used' => $this->tenantId ? TenantUser::class : User::class,
        //     'user_found' => $user ? true : false,
        //     'user_email' => $user?->email,
        // ]);

        // if (! $user) {
        //     Log::warning('[NotificationSent] No user found, skipping ->notify() call', [
        //         'userId' => $userId,
        //         'tenantId' => $this->tenantId,
        //         'db_connection' => DB::connection()->getDatabaseName(),
        //     ]);
        // }

        $user?->notify(new GeneralNotification($message, $type, $link, $senderId, $tenantId, $admin));
    }

    /**
     * tenantId thakle TenantUser (tenant DB) theke, na thakle User (central DB) theke resolve kora
     */
    protected function resolveUser()
    {
        if ($this->tenantId) {
            return TenantUser::find($this->userId);
        }

        return User::find($this->userId);
    }

    public function broadcastOn()
    {
        $channel = $this->tenantId
            ? 'tenant.'.$this->tenantId.'.user.'.$this->userId
            : 'central.user.'.$this->userId;

        Log::info('[NotificationSent] broadcastOn resolved channel', [
            'channel' => $channel,
            'tenantId' => $this->tenantId,
            'userId' => $this->userId,
        ]);

        return new PrivateChannel($channel);
    }

    public function broadcastAs()
    {
        return 'notification.sent';
    }

    public function broadcastWith()
    {
        $user = $this->resolveUser();
        $latestNotification = $user?->notifications()->latest()->first();

        $payload = [
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

        Log::info('[NotificationSent] broadcastWith payload', $payload);

        return $payload;
    }
}
