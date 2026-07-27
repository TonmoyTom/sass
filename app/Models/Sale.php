<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sale extends Model
{
    use Filterable;

    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id', 'seller_id', 'module_id', 'module_tier_id',
        'sale_type', 'amount', 'commission_amount', 'admin_amount',
        'status', 'sold_at', 'invoice_number', 'payment_method', 'transaction_id', 'free_renewal_note', 'free_renewed_by', 'is_free_renewal',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'admin_amount' => 'decimal:2',
        'sold_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function free_renewed_by()
    {
        return $this->belongsTo(User::class, 'free_renewed_by');
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
        return $this->hasOne(Commission::class, 'sale_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Sale $sale) {
            if (! $sale->invoice_number) {
                $sale->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';

        do {
            $number = $prefix.strtoupper(Str::random(6));
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }
}
