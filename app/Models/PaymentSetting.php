<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    

    protected $fillable = [
        'method', 'is_active', 'merchant_number', 'api_key', 'api_secret',
        'username', 'password', 'bank_name', 'account_name', 'account_number',
        'routing_number', 'branch', 'instructions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_secret', 'password',
    ];
}
