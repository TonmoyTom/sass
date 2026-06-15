<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Nwidart\Modules\Module;

class Seller extends Model
{
    protected $fillable = [
        'user_id', 'referral_code', 'status', 'commission_rate',
        'total_sales', 'total_earned', 'bank_name', 'bank_account',
        'bkash_number', 'nid_number', 'nid_verified', 'joined_at',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'nid_verified' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    public function moduleRequests()
    {
        return $this->hasMany(SellerModuleRequest::class);
    }

    public function approvedModules()
    {
        return $this->belongsToMany(ModulePackage::class, 'seller_module_requests')
            ->wherePivot('status', 'approved')
            ->withPivot('status', 'reviewed_at')
            ->withTimestamps();
    }
}
