<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\Concerns\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 *
 * Central database user model.
 * Three user types: super_admin, seller, tenant_owner.
 *
 * Authentication is handled via multi-guards (see config/auth.php).
 */
class User extends Authenticatable
{
    use HasApiTokens;     // For future mobile app
    use HasFactory;
    use HasFiles;          // For avatar uploads (from our reusable trait)
    use HasRoles;          // Spatie Permission
    use Notifiable;        // For notifications
    // use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'user_type',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'user_type' => UserType::class,
            'status' => UserStatus::class,
        ];
    }

    /**
     * File attributes (used by HasFiles trait).
     */
    protected array $fileAttributes = [
        'avatar' => 'avatars',
    ];

    // ─────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────


    public function userInfo()
    {
        return $this->hasOne(\App\Models\UserInfo::class);
    }
    /**
     * Tenants owned by this user (for tenant_owner type).
     */
    public function ownedTenants()
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    /**
     * Seller profile (for seller type).
     */
    public function sellerProfile()
    {
        return $this->hasOne(\App\Models\Seller::class);
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    /**
     * Scope: Only super admins.
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('user_type', UserType::SUPER_ADMIN);
    }

    /**
     * Scope: Only sellers.
     */
    public function scopeSellers($query)
    {
        return $query->where('user_type', UserType::SELLER);
    }

    /**
     * Scope: Only tenant owners.
     */
    public function scopeTenantOwners($query)
    {
        return $query->where('user_type', UserType::TENANT_OWNER);
    }

    /**
     * Scope: Only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::ACTIVE);
    }

    // ─────────────────────────────────────────────
    // TYPE CHECKS
    // ─────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->user_type === UserType::SUPER_ADMIN;
    }

    public function isSeller(): bool
    {
        return $this->user_type === UserType::SELLER;
    }

    public function isTenantOwner(): bool
    {
        return $this->user_type === UserType::TENANT_OWNER;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    /**
     * Get user's avatar URL.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            // Default avatar from initials (Gravatar-like)
            return $this->getDefaultAvatarUrl();
        }

        return $this->getFileUrl('avatar');
    }

    /**
     * Generate default avatar URL (UI Avatars service).
     */
    protected function getDefaultAvatarUrl(): string
    {
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=6366f1&color=fff&size=200";
    }

    /**
     * Get user's initials.
     */
    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))
            ->take(2)
            ->map(fn($word) => strtoupper(substr($word, 0, 1)))
            ->implode('');
    }

    /**
     * Update last login info.
     */
    public function recordLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Get redirect URL based on user type.
     */
    public function getDashboardUrl(): string
    {
        return match($this->user_type) {
            UserType::SUPER_ADMIN => route('admin.dashboard'),
            UserType::SELLER => route('seller.dashboard'),
            UserType::TENANT_OWNER => $this->getTenantDashboardUrl(),
        };
    }

    /**
     * Get tenant owner's first tenant dashboard.
     */
    protected function getTenantDashboardUrl(): string
    {
        $tenant = $this->ownedTenants()->first();

        if (!$tenant) {
            return route('tenant.signup'); // No tenant yet, redirect to create one
        }

        return $tenant->url ?? '/';
    }
}
