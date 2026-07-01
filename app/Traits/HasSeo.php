<?php

namespace App\Traits;

use App\Models\CompanySetting;
use App\Models\SeoMeta;
use App\Models\TenantSeoMeta;

trait HasSeo
{
    public function seo()
    {
        return $this->morphOne($this->seoModel(), 'seoable');
    }

    protected function seoModel(): string
    {
        return function_exists('tenant') && tenant()
            ? TenantSeoMeta::class
            : SeoMeta::class;
    }

    public function seoArray(): array
    {
        return [
            'meta_title' => $this->seo?->meta_title,
            'meta_description' => $this->seo?->meta_description,
            'meta_keywords' => $this->seo?->meta_keywords,
            'canonical_url' => $this->seo?->canonical_url,
            'robots' => $this->seo?->robots ?? 'index,follow',
            'og_title' => $this->seo?->og_title,
            'og_description' => $this->seo?->og_description,
            'og_image' => $this->seoImageUrl($this->seo?->og_image),
            'og_type' => $this->seo?->og_type ?? 'website',
            'twitter_title' => $this->seo?->twitter_title,
            'twitter_description' => $this->seo?->twitter_description,
            'twitter_image' => $this->seoImageUrl($this->seo?->twitter_image),
            'twitter_card' => $this->seo?->twitter_card ?? 'summary_large_image',
            'schema_type' => $this->seo?->schema_type,
            'schema_data' => $this->seo?->schema_data,
            'focus_keyword' => $this->seo?->focus_keyword,
            'sitemap_include' => $this->seo?->sitemap_include ?? true,
            'sitemap_priority' => $this->seo?->sitemap_priority ?? 0.5,
            'sitemap_frequency' => $this->seo?->sitemap_frequency ?? 'weekly',
        ];
    }

    public function frontSeoArray(): array
    {
        $seo = $this->seoArray();
        $defaultTitle = $this->defaultSeoTitle();

        $title = $seo['meta_title']
            ? $seo['meta_title'].' - '.$defaultTitle
            : $defaultTitle;

        return [
            'title' => $title,
            'description' => $seo['meta_description'] ?: str($this->description ?? '')->limit(160)->toString(),
            'canonical' => $seo['canonical_url'] ?? url()->current(),
            'robots' => $seo['robots'],
            'og_title' => $seo['og_title'] ?: $seo['meta_title'] ?: $defaultTitle,
            'og_description' => $seo['og_description'] ?: $seo['meta_description'],
            'og_image' => $seo['og_image'],
            'og_type' => $seo['og_type'],
            'twitter_card' => $seo['twitter_card'],
            'schema_type' => $seo['schema_type'],
            'schema_data' => $seo['schema_data'],
        ];
    }

    /**
     * Default title — tenant context hole CompanySetting theke company_name,
     * na hole app name.
     */
    protected function defaultSeoTitle(): string
    {
        if (function_exists('tenant') && tenant()) {
            $companyName = CompanySetting::select('company_name')->first()?->company_name;

            return $companyName;
        }

        return config('app.name');
    }

    protected function seoImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

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
