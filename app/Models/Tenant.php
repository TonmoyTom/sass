<?php

declare(strict_types=1);

namespace App\Models;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * Tenant Model
 *
 * Represents a tenant (school) on the platform.
 * Each tenant has their own database for full isolation.
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Custom columns to be saved on the tenants table.
     * (In addition to the default 'id' and 'data' columns)
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'subdomain',
            'database_name',
            'owner_id',
            'email',
            'phone',
            'status',
            'trial_ends_at',
            'suspended_at',
            'suspended_reason',
            'referred_by',
        ];
    }

    /**
     * Cast attributes to native types.
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'trial_ends_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    /**
     * Get the owner user of this tenant.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Check if tenant is on trial.
     */
    public function onTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant is active (paid or trial).
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trial']);
    }

    /**
     * Check if tenant is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Get the primary subdomain for this tenant.
     */
    public function getSubdomainAttribute(): ?string
    {
        $domain = $this->domains()->first();
        if (! $domain) {
            return null;
        }

        // Extract subdomain from full domain
        $parts = explode('.', $domain->domain);

        return $parts[0] ?? null;
    }

    /**
     * Get full URL for this tenant.
     */
    public function getUrlAttribute(): ?string
    {
        $domain = $this->domains()->first();
        if (! $domain) {
            return null;
        }

        $protocol = config('app.env') === 'production' ? 'https' : 'http';

        return "{$protocol}://{$domain->domain}";
    }

    /**
     * Suspend the tenant.
     */
    public function suspend(?string $reason = null): void
    {
        $this->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_reason' => $reason,
        ]);
    }

    /**
     * Reactivate the tenant.
     */
    public function reactivate(): void
    {
        $this->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspended_reason' => null,
        ]);
    }

    public function hasModule(string $moduleAlias): bool
    {
        return TenantModule::on('mysql')   // ← central connection
            ->where('tenant_id', $this->id)
            ->whereHas('module', fn ($q) => $q->where('alias', $moduleAlias))
            ->whereIn('status', ['active', 'purchased', 'trial'])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function enabledModules(): array
    {
        return TenantModule::on('mysql')   // ← central connection
            ->where('tenant_id', $this->id)
            ->whereIn('status', ['active', 'purchased', 'trial'])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->with('module')
            ->get()
            ->pluck('module.alias')
            ->filter()
            ->values()
            ->all();
    }
}
