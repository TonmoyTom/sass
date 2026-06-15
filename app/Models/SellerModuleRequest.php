<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerModuleRequest extends Model
{
    protected $fillable = [
        'seller_id',
        'module_id',
        'status',
        'note',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function module()
    {
        return $this->belongsTo(ModulePackage::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // scope helpers
    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    public function scopeApproved($q)
    {
        return $q->where('status', 'approved');
    }
}
