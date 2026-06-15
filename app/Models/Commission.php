<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'seller_id', 'sale_id', 'amount', 'rate',
        'commission_type', 'status', 'hold_until',
        'approved_at', 'paid_at', 'notes',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'rate'        => 'decimal:2',
        'hold_until'  => 'datetime',
        'approved_at' => 'datetime',
        'paid_at'     => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // scope helpers
    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    public function scopeAvailable($q)
    {
        return $q->where('status', 'approved');
    }
}
