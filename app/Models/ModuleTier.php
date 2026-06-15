<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleTier extends Model
{
    protected $fillable = [
        'module_id',
        'name',
        'slug',
        'limits',
        'monthly_price',
        'yearly_price',
        'one_time_price',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    protected $casts = [
        'limits'         => 'array',
        'monthly_price'  => 'decimal:2',
        'yearly_price'   => 'decimal:2',
        'one_time_price' => 'decimal:2',
        'is_active'      => 'boolean',
        'is_popular'     => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(ModulePackage::class, 'module_id');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
