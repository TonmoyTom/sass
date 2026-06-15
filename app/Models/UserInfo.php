<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'bio',
        'country', 'city', 'postal_code', 'nt_id', 'tx_id',
        'facebook', 'twitter', 'lnkedin', 'instagram',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
