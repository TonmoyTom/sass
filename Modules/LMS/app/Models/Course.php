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
        'preview_video_source', 'preview_video_url', 'preview_video_path','preview_image',
        'is_free', 'price', 'sequential_unlock', 'status', 'sort_order','short_description', 'discount_price' , 'live_class_starts_at'
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'sequential_unlock' => 'boolean',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'live_class_starts_at' => 'datetime',
    ];

    protected $appends = [
        'thumbnail_url',
        'preview_image_url',
        'resolved_preview_video_url',
    ];

    public function hasDiscount(): bool
        {
            return ! $this->is_free
                && $this->discount_price !== null
                && (float) $this->discount_price < (float) $this->price;
        }

        /**
         * The price to actually charge/display as "the" price — discount_price
         * when a valid discount is set, otherwise the regular price.
         */
        public function getEffectivePriceAttribute(): ?float
        {
            if ($this->is_free) {
                return 0;
            }

            return $this->hasDiscount() ? (float) $this->discount_price : (float) $this->price;
        }

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

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'course_id');
    }

    public function modules()
    {
        return $this->hasMany(CourseModule::class, 'course_id')->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(CourseReview::class, 'course_id')->latest();
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->avg('rating');

        return $avg ? round((float) $avg, 1) : null;
    }

    public function reviewsCount(): int
    {
        return $this->reviews()->count();
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


    public function faqs()
    {
        return $this->hasMany(CourseFaq::class, 'course_id')->orderBy('sort_order');
    }

    public function instructors()
       {
           return $this->belongsToMany(TenantUser::class, 'course_instructors', 'course_id', 'instructor_id')
               ->withTimestamps();
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
    public function getPreviewImageUrlAttribute(): ?string
    {
        if ($this->preview_image) {
            return $this->assetUrl($this->preview_image);
        }

        return null;

    }

    public function hasPreviewVideo(): bool
    {
        return ! empty($this->preview_video_url) || ! empty($this->preview_video_path);
    }


    public function getResolvedPreviewVideoUrlAttribute(): ?string
    {
        if ($this->preview_video_source === 'upload' && $this->preview_video_path) {
            return $this->assetUrl($this->preview_video_path);
        }

        if ($this->preview_video_url) {
            return $this->toYoutubeEmbedUrl($this->preview_video_url) ?? $this->preview_video_url;
        }

        return null;
    }

    protected function toYoutubeEmbedUrl(string $url): ?string
    {
        $pattern = '#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})#';

        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
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
