<?php

namespace Modules\LMS\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CourseSubcategory extends Model
{
    use Filterable;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (CourseSubcategory $subcategory) {
            if (! $subcategory->slug) {
                $subcategory->slug = Str::slug($subcategory->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'subcategory_id');
    }
}
