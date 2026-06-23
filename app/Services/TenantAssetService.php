<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Tenant;
use App\Models\TenantUser;

class TenantAssetService
{
    /**
     * Tenant er domain diye full asset URL banay.
     * Central domain theke o kaaj kore (localhost na, tenant domain boshay).
     *
     * @param  string|null  $path  e.g. 'company/abc.png'
     */
    public function url(Tenant $tenant, ?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $domain = $tenant->domains->first()?->domain;
        if (! $domain) {
            return null;
        }

        $scheme = request()->getScheme();
        $port = request()->getPort();
        $portPart = in_array($port, [80, 443]) ? '' : ':'.$port;

        return "{$scheme}://{$domain}{$portPart}/tenancy/assets/".ltrim($path, '/');
    }

    public function companyLogo(Tenant $tenant): ?string
    {
        $logoPath = null;

        $tenant->run(function () use (&$logoPath) {
            $settings = CompanySetting::select('company_name', 'logo')
                ->first();
            $logoPath = $settings?->logo;
        });

        return $this->url($tenant, $logoPath);
    }

    public function ownerAvatar(Tenant $tenant): ?string
    {
        $avatarPath = null;

        $tenant->run(function () use (&$avatarPath) {
            $user = TenantUser::select('avatar')->first();
            $avatarPath = $user?->avatar;
        });

        return $this->url($tenant, $avatarPath);
    }
}
