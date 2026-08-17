<?php

namespace Modules\LMS\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CourseCategory extends Model
{
    use Filterable;

    protected $fillable = [
        'name', 'slug', 'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (CourseCategory $category) {
            if (! $category->slug) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id');
    }

    public function subcategories()
    {
        return $this->hasMany(CourseSubcategory::class, 'category_id')->orderBy('sort_order');
    }
}
