<?php

namespace Modules\LMS\Models;

use App\Models\TenantUser;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = ['certificate_number', 'course_id', 'enrollment_id', 'student_id', 'file_path', 'issued_at'];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function student()
    {
        return TenantUser::find($this->student_id);
    }

    public static function generateNumber(): string
    {
        // e.g. CERT-8F3K2Q9X — short, unambiguous (no 0/O/1/I confusion in the random part would need a custom alphabet; keep simple uppercase hex for now)
        do {
            $number = 'CERT-'.strtoupper(bin2hex(random_bytes(4)));
        } while (self::where('certificate_number', $number)->exists());

        return $number;
    }
}