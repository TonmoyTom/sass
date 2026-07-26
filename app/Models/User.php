<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\Concerns\HasFiles;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use PragmaRX\Google2FA\Google2FA;
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
    use Filterable;
    use HasApiTokens;

    // For future mobile app
    use HasFactory;
    use HasFiles;          // For avatar uploads (from our reusable trait)
    use HasRoles;

    // Spatie Permission
    use Notifiable;       // For notifications
    // use SoftDeletes;

    protected $connection = 'central';

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
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
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

    protected $appends = [
        'avatar_url',
    ];

    // ─────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────

    public function info()
    {
        return $this->hasOne(UserInfo::class);
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
        return $this->hasOne(Seller::class);
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
        if (! $this->avatar) {
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
        return asset('logo/default-avatar.png');
    }

    /**
     * Get user's initials.
     */
    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))
            ->take(2)
            ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
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
        return match ($this->user_type) {
            UserType::SUPER_ADMIN => route('admin.dashboard'),
            UserType::SELLER => route('seller.dashboard'),
            UserType::TENANT_OWNER => route('tenant.central.dashboard'),
            UserType::STAFF => route('admin.dashboard'),
        };
    }

    public function getTwoFactorPage(): string
    {
        return match ($this->user_type) {
            UserType::SUPER_ADMIN => 'Auth/TwoFactor/Show',
            UserType::SELLER => 'Auth/Seller/Show',
            UserType::TENANT_OWNER => 'Auth/Tenant/Show',
            UserType::STAFF => 'Auth/TwoFactor/Show',
        };
    }

    /**
     * Get tenant owner's first tenant dashboard.
     */
    protected function getTenantDashboardUrl(): string
    {
        $tenant = $this->ownedTenants()->first();

        if (! $tenant) {
            return route('tenant.signup'); // No tenant yet, redirect to create one
        }

        return $tenant->url ?? '/';
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at);
    }

    public function twoFactorQrCodeUrl(): string
    {
        $google2fa = new Google2FA;

        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            decrypt($this->two_factor_secret)
        );
    }

    public function generateRecoveryCodes(): array
    {
        $codes = collect(range(1, 8))->map(fn () => strtoupper(Str::random(10)))->all();

        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();

        return $codes;
    }

    public function replaceRecoveryCode(string $code): void
    {
        $codes = json_decode(decrypt($this->two_factor_recovery_codes), true);

        $codes = array_filter($codes, fn ($c) => $c !== $code);

        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
        ])->save();
    }
}
