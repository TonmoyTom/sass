<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModulePackage extends Model
{
    protected $table = 'modules';

    protected $fillable = [
        'name', 'alias', 'description', 'icon', 'version',
        'pricing_type', 'monthly_price', 'yearly_price', 'one_time_price',
        'commission_rate', 'module_category', 'is_active', 'is_core',
        'sort_order', 'features', 'dependencies',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'one_time_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'features' => 'array',
        'dependencies' => 'array',
    ];

    public function sellerRequests()
    {
        return $this->hasMany(SellerModuleRequest::class, 'module_id');
    }

    public function tiers()
    {
        return $this->hasMany(ModuleTier::class, 'module_id')->orderBy('sort_order');
    }

    public function cheapestTier()
    {
        return $this->hasOne(ModuleTier::class, 'module_id')
            ->where('is_active', true)
            ->orderBy('monthly_price');
    }
}
