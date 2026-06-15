<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'tenant_id', 'seller_id', 'module_id', 'module_tier_id',
        'sale_type', 'amount', 'commission_amount', 'admin_amount',
        'status', 'sold_at',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'admin_amount'      => 'decimal:2',
        'sold_at'           => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function module()
    {
        return $this->belongsTo(ModulePackage::class, 'module_id');
    }

    public function tier()
    {
        return $this->belongsTo(ModuleTier::class, 'module_tier_id');
    }

    public function commission()
    {
        return $this->hasOne(Commission::class);
    }
}
