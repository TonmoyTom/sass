<?php

namespace App\Models;

use App\Models\Concerns\HasFiles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class TenantUser extends Authenticatable
{
    use HasRoles ,  HasFiles;

    protected $table = 'users';

    protected $guard_name = 'web';

    protected $fillable = ['name', 'email', 'password', 'phone', 'avatar'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

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
}
