<?php

namespace App\Models;

use App\Models\Concerns\HasFiles;
use App\Traits\Filterable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;
use Spatie\Permission\Traits\HasRoles;

class TenantUser extends Authenticatable
{
    use Filterable ,  HasFiles, HasRoles ,Notifiable;

    protected $table = 'users';

    protected $guard_name = 'tenant';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar',
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected $casts = [
        'password' => 'hashed',
        'two_factor_confirmed_at' => 'datetime',
    ];

    protected array $fileAttributes = [
        'avatar' => 'avatars',
    ];

    protected $appends = [
        'avatar_url',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {

            return $this->getDefaultAvatarUrl();
        }

        return $this->getFileUrl('avatar');
    }

    protected function getDefaultAvatarUrl(): string
    {
        $name = urlencode($this->name);

        return "https://ui-avatars.com/api/?name={$name}&background=6366f1&color=fff&size=200";
    }

    public function info()
    {
        return $this->hasOne(TenantUserInfo::class, 'user_id');
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
