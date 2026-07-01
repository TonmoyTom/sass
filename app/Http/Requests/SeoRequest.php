<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url',
            'robots' => 'nullable',

            'og_title' => 'nullable|string|max:60',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'og_type' => 'nullable|string',

            'twitter_title' => 'nullable|string|max:60',
            'twitter_description' => 'nullable|string|max:160',
            'twitter_card' => 'nullable|in:summary,summary_large_image',

            'schema_type' => 'nullable|string',
            'schema_data' => 'nullable|array',
            'focus_keyword' => 'nullable|string|max:100',

            'sitemap_include' => 'nullable|boolean',
            'sitemap_priority' => 'nullable|numeric|min:0|max:1',
            'sitemap_frequency' => 'nullable|in:always,hourly,daily,weekly,monthly,yearly,never',
        ];
    }
}
