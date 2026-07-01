<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{

    protected $table = 'seo_metas'; 
    protected $fillable = [
        'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'robots',
        'og_title', 'og_description', 'og_image', 'og_type',
        'twitter_title', 'twitter_description', 'twitter_image', 'twitter_card',
        'schema_type', 'schema_data', 'sitemap_include', 'sitemap_priority',
        'sitemap_frequency', 'focus_keyword',
    ];

    protected $casts = [
        'schema_data' => 'array',
        'sitemap_include' => 'boolean',
    ];

    public function seoable()
    {
        return $this->morphTo();
    }
}
