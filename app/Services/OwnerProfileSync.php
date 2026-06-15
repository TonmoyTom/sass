<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;

class OwnerProfileSync
{
    public function syncToTenant(User $owner, array $data, ?string $originalEmail = null): void
    {
        $matchEmail = $originalEmail ?? $owner->email;

        foreach ($owner->ownedTenants as $tenant) {
            $tenant->run(function () use ($owner, $data, $matchEmail) {
                $tenantUser = TenantUser::where('email', $matchEmail)->first();

                if (! $tenantUser) {
                    return;
                }
                $tenantUser->update([
                    'name' => $owner->name,
                    'email' => $data['email'] ?? $tenantUser->email,
                    'phone' => $data['phone'] ?? $tenantUser->phone,
                    'avatar' => $data['avatar'] ?? $tenantUser->avatar,
                ]);

                $tenantUser->info()->updateOrCreate(
                    ['user_id' => $tenantUser->id],
                    [
                        'first_name' => $data['first_name'] ?? $tenantUser->info?->first_name,
                        'last_name' => $data['last_name'] ?? $tenantUser->info?->last_name,
                        'bio' => $data['bio'] ?? $tenantUser->info?->bio,
                        'country' => $data['country'] ?? $tenantUser->info?->country,
                        'city' => $data['city'] ?? $tenantUser->info?->city,
                        'postal_code' => $data['postal_code'] ?? $tenantUser->info?->postal_code,
                        'facebook' => $data['facebook'] ?? $tenantUser->info?->facebook,
                        'twitter' => $data['twitter'] ?? $tenantUser->info?->twitter,
                        'lnkedin' => $data['linkedin'] ?? $tenantUser->info?->lnkedin,
                        'instagram' => $data['instagram'] ?? $tenantUser->info?->instagram,
                    ]
                );
            });
        }

    }

    public function syncToCentral(Tenant $tenant, TenantUser $tenantUser, array $data, ?string $originalEmail = null): void
    {
        $matchEmail = $originalEmail ?? $tenantUser->email;

        $owner = $tenant->owner;
        if (! $owner || $owner->email !== $matchEmail) {
            return;
        }

        $owner->update([
            'name' => $data['name'] ?? $owner->name,
            'email' => $data['email'] ?? $owner->email,
            'phone' => $data['phone'] ?? $owner->phone,
            'avatar' => $data['avatar'] ?? $owner->avatar,
        ]);

        $info = $owner->info()->updateOrCreate(
            ['user_id' => $owner->id],
            [
                'first_name' => $data['first_name'] ?? $owner->info?->first_name,
                'last_name' => $data['last_name'] ?? $owner->info?->last_name,
                'bio' => $data['bio'] ?? $owner->info?->bio,
                'country' => $data['country'] ?? $owner->info?->country,
                'city' => $data['city'] ?? $owner->info?->city,
                'postal_code' => $data['postal_code'] ?? $owner->info?->postal_code,
                'facebook' => $data['facebook'] ?? $owner->info?->facebook,
                'twitter' => $data['twitter'] ?? $owner->info?->twitter,
                'lnkedin' => $data['linkedin'] ?? $owner->info?->lnkedin,  // central typo column
                'instagram' => $data['instagram'] ?? $owner->info?->instagram,
            ]
        );

    }
}
