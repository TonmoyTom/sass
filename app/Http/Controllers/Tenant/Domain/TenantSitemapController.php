<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class TenantSitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create();

        // Static/CMS pages (site_settings table theke)
        SiteSetting::query()
            ->with('seo')
            ->get()
            ->each(function (SiteSetting $setting) use ($sitemap) {
                if ($setting->seo && ! $setting->seo->sitemap_include) {
                    return;
                }

                $sitemap->add(
                    Url::create(url($setting->page_url ?? '/'))
                        ->setLastModificationDate($setting->updated_at)
                        ->setPriority($setting->seo?->sitemap_priority ?? 0.5)
                        ->setChangeFrequency($setting->seo?->sitemap_frequency ?? Url::CHANGE_FREQUENCY_WEEKLY)
                );
            });

        // Future e — Ecommerce Product / LMS Course public pages ekhane add hobe
        // $this->addProducts($sitemap);
        // $this->addCourses($sitemap);

        return $sitemap->toResponse(request());
    }

    // Example — future module er jonno
    // private function addProducts(Sitemap $sitemap): void
    // {
    //     \App\Models\Product::where('is_active', true)
    //         ->with('seo')
    //         ->get()
    //         ->each(function ($product) use ($sitemap) {
    //             if ($product->seo && ! $product->seo->sitemap_include) {
    //                 return;
    //             }
    //
    //             $sitemap->add(
    //                 Url::create(route('tenant.products.show', $product->slug))
    //                     ->setLastModificationDate($product->updated_at)
    //                     ->setPriority($product->seo?->sitemap_priority ?? 0.6)
    //                     ->setChangeFrequency($product->seo?->sitemap_frequency ?? Url::CHANGE_FREQUENCY_WEEKLY)
    //             );
    //         });
    // }

    public function robots()
    {

        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /settings\n";
        $content .= "Disallow: /dashboard\n";
        $content .= "Disallow: /roles\n";
        $content .= "\n";
        $content .= 'Sitemap: '.url('/sitemap.xml');


        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }
}
