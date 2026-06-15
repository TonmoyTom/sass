<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class TenantUser extends Authenticatable
{
    use HasRoles;

    protected $table = 'users';

    protected $guard_name = 'web';

    protected $fillable = ['name', 'email', 'password', 'phone', 'avatar'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    public function info()
    {
        return $this->hasOne(TenantUserInfo::class, 'user_id');
    }
}
