<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantModule extends Model
{
    protected $fillable = [
        'tenant_id', 'module_id', 'module_tier_id',
        'status', 'access_type', 'limits',
        'activated_at', 'purchased_at', 'expires_at',
        'price_paid', 'billing_cycle',
    ];

    protected $casts = [
        'limits' => 'array',
        'activated_at' => 'datetime',
        'purchased_at' => 'datetime',
        'expires_at' => 'datetime',
        'price_paid' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function module()
    {
        return $this->belongsTo(ModulePackage::class, 'module_id');
    }

    public function tier()
    {
        return $this->belongsTo(ModuleTier::class, 'module_tier_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
