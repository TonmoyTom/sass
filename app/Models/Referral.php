<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = [
        'seller_id', 'tenant_id', 'referral_code',
        'ip_address', 'user_agent', 'landing_url', 'converted_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
