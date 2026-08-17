<?php

namespace Modules\LMS\Models;

use App\Models\TenantUser;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use Filterable;

    protected $fillable = [
        'category_id', 'subcategory_id', 'instructor_id',
        'title', 'slug', 'description', 'thumbnail',
        'is_free', 'price', 'sequential_unlock', 'status', 'sort_order',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'sequential_unlock' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected $appends = [
        'thumbnail_url',
    ];

    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            if (! $course->slug) {
                $course->slug = Str::slug($course->title).'-'.Str::random(6);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(CourseSubcategory::class, 'subcategory_id');
    }

    public function modules()
    {
        return $this->hasMany(CourseModule::class, 'course_id')->orderBy('sort_order');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, CourseModule::class, 'course_id', 'course_module_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'course_id');
    }

    public function orders()
    {
        return $this->hasMany(CourseOrder::class, 'course_id');
    }

    // instructor — TenantUser, alada connection/model, direct relation na kore accessor
    public function getInstructorAttribute()
    {
        return $this->instructor_id
            ? TenantUser::find($this->instructor_id)
            : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->thumbnail) {
            return $this->assetUrl($this->thumbnail);
        }

        return null;

    }

    protected function assetUrl(string $path): string
    {

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $path = preg_replace('#^storage/#', '', $path);

        if (function_exists('tenant') && tenant()) {
            return url('/tenancy/assets/'.$path);
        }

        return asset('storage/'.$path);
    }
}
