<?php

namespace App\Http\Controllers;

use App\Models\ModulePackage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class CentralSitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create();

        $sitemap->add(
            Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );

        $sitemap->add(
            Url::create('/pricing')
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        );

        $sitemap->add(
            Url::create(route('modules.index'))
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );

        ModulePackage::query()
            ->where('is_active', true)
            ->with('seo')
            ->get()
            ->each(function (ModulePackage $module) use ($sitemap) {
                if ($module->seo && ! $module->seo->sitemap_include) {
                    return;
                }

                $sitemap->add(
                    Url::create(route('modules.show', $module->alias))
                        ->setLastModificationDate($module->updated_at)
                        ->setPriority($module->seo?->sitemap_priority ?? 0.6)
                        ->setChangeFrequency($module->seo?->sitemap_frequency ?? Url::CHANGE_FREQUENCY_WEEKLY)
                );
            });

        return $sitemap->toResponse(request());
    }
}
