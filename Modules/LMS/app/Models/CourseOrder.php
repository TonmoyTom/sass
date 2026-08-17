<?php

namespace Modules\LMS\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CourseOrder extends Model
{
    use Filterable;

    protected $fillable = [
        'course_id', 'student_id', 'amount', 'status',
        'payment_method', 'transaction_id', 'purchased_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (CourseOrder $order) {
            if (! $order->invoice_number) {
                $order->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'LMS-'.now()->format('Ym').'-';

        do {
            $number = $prefix.strtoupper(Str::random(6));
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function enrollment()
    {
        return $this->hasOne(Enrollment::class, 'order_id');
    }

    public function getStudentAttribute()
    {
        return \App\Models\TenantUser::find($this->student_id);
    }
}
