<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUserInfo extends Model
{
    protected $table = 'user_infos';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'bio',
        'country',
        'city',
        'postal_code',
        'nt_id',
        'tx_id',
        'facebook',
        'twitter',
        'linkedin',
        'instagram',
    ];

    public function user()
    {
        return $this->belongsTo(TenantUser::class, 'user_id');
    }
}
