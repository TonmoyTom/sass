<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
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


    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
