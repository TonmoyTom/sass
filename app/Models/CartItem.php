<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'module_id', 'module_tier_id',
        'billing_cycle', 'price', 'referral_code',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function module()
    {
        return $this->belongsTo(ModulePackage::class, 'module_id');
    }

    public function tier()
    {
        return $this->belongsTo(ModuleTier::class, 'module_tier_id');
    }
}
