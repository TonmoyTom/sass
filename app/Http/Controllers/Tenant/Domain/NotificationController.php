<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $this->mergedNotifications();

        return response()->json($notifications);
    }

    /**
     * Notification mark-as-read — source (tenant/central) onujayi thik model e mark kore.
     */
    public function markRead(Request $request, string $tenant, string $id)
    {
        $source = $request->input('source');

        if ($source === 'tenant') {
            $tenantUser = auth('tenant')->user();

            $tenantUser?->notifications()
                ->where('id', $id)
                ->first()
                ?->markAsRead();
        } elseif ($source === 'central') {
            $ownerId = tenant('owner_id');

            // 'mysql' connection tenancy active thakle tenant DB-r dike point kore,
            // tai central() closure diye explicit central context e dhukte hobe
            tenancy()->central(function () use ($ownerId, $id) {
                $owner = User::find($ownerId);
                $owner?->notifications()->where('id', $id)->first()?->markAsRead();
            });
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    /**
     * sender_id thakle central User theke sender-er live naam o avatar resolve kore.
     * sender_id na thakle 'System' + null return kore (frontend default avatar dekhabe).
     *
     * @return array{0: string, 1: ?string}
     */
    protected function resolveSenderDisplay(?int $senderId): array
    {
        if (! $senderId) {
            return ['System', null];
        }

        // sender shobsomoy central admin, tai central() closure diye lookup
        return tenancy()->central(function () use ($senderId) {
            $sender = User::find($senderId);

            if (! $sender) {
                return ['System', null];
            }

            $avatarUrl = $sender->avatar
                ? Storage::disk('public')->url($sender->avatar)
                : null;

            return [$sender->name, $avatarUrl];
        });
    }

    protected function mergedNotifications()
    {
        // 1. Tenant DB notification — logged-in tenant user
        $tenantUser = auth('tenant')->user();

        $tenantNotifications = $tenantUser
            ? $tenantUser->notifications()
                ->latest()
                ->take(20)
                ->get()
                ->map(function ($n) use ($tenantUser) {
                    $isFromAdmin = ($n->data['admin'] ?? null) === 'admin';
                    $senderId = $n->data['sender_id'] ?? null;

                    if ($isFromAdmin && $senderId) {
                        [$displayName, $displayAvatar] = $this->resolveSenderDisplay($senderId);
                    } else {
                        $displayName = $tenantUser->name;
                        $displayAvatar = $tenantUser->avatar_url ?? null;
                    }

                    return [
                        'id' => $n->id,
                        'source' => 'tenant',
                        'data' => [
                            'message' => $n->data['message'] ?? '',
                            'type' => $n->data['type'] ?? 'info',
                            'link' => $n->data['link'] ?? null,
                        ],
                        'read_at' => $n->read_at,
                        'created_at' => $n->created_at,
                        'user' => [
                            'name' => $displayName,
                            'avatar_url' => $displayAvatar,
                        ],
                    ];
                })
            : collect();
        $ownerId = tenant('owner_id');
        $centralNotifications = collect();
        if ($ownerId) {
            $centralNotifications = tenancy()->central(function () use ($ownerId) {
                $owner = User::find($ownerId);
                if (! $owner) {
                    return collect();
                }
                $avatarUrl = $owner->avatar
                    ? Storage::disk('public')->url($owner->avatar)
                    : null;
                return $owner->notifications()
                    ->latest()
                    ->take(20)
                    ->get()
                    ->map(function ($n) use ($avatarUrl) {
                        $senderId = $n->data['sender_id'] ?? null;
                        $sender = $senderId ? User::find($senderId) : null;
                        return [
                            'id' => $n->id,
                            'source' => 'central',
                            'data' => [
                                'message' => $n->data['message'] ?? '',
                                'type' => $n->data['type'] ?? 'info',
                                'link' => $n->data['link'] ?? null,
                            ],
                            'read_at' => $n->read_at,
                            'created_at' => $n->created_at,
                            'user' => [
                                'name' => $sender?->name ?? 'System',
                                'avatar_url' => $sender && $sender->avatar
                                    ? Storage::disk('public')->url($sender->avatar)
                                    : $avatarUrl,
                            ],
                        ];
                    });
            });
        }

        // 3. Merge + sort
        return $tenantNotifications
            ->concat($centralNotifications)
            ->sortByDesc('created_at')
            ->values();
    }
}
