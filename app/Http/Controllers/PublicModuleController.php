<?php

namespace App\Http\Controllers;

use App\Models\ModulePackage;
use Inertia\Inertia;
use Inertia\Response;

class PublicModuleController extends Controller
{
    /**
     * Public module marketplace listing page.
     */
    public function index(): Response
    {
        $modules = ModulePackage::query()
            ->where('is_active', true)
            ->with(['tiers' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ModulePackage $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'alias' => $m->alias,
                'icon' => $m->icon,
                'description' => $m->description,
                'category' => $m->module_category,
                'pricing_type' => $m->pricing_type,
                'starting_price' => $m->tiers->min('monthly_price'),
            ])
            ->values();

        return Inertia::render('Public/Modules/Index', [
            'modules' => $modules,
            'seo' => [
                'title' => 'Modules & Add-ons Marketplace',
                'description' => 'Explore all available modules and add-ons to extend your platform.',
                'canonical' => url('/modules'),
                'robots' => 'index,follow',
                'og_title' => 'Modules & Add-ons Marketplace',
                'og_description' => 'Explore all available modules and add-ons to extend your platform.',
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image',
            ],
        ]);
    }

    /**
     * Public single module detail page.
     */
    public function show(ModulePackage $module): Response
    {
        abort_unless($module->is_active, 404);

        $module->load([
            'seo',
            'tiers' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ]);

        return Inertia::render('Public/Modules/Show', [
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'alias' => $module->alias,
                'icon' => $module->icon,
                'description' => $module->description,
                'version' => $module->version,
                'category' => $module->module_category,
                'pricing_type' => $module->pricing_type,
                'features' => $module->features ?? [],
                'tiers' => $module->tiers->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'monthly_price' => $t->monthly_price,
                    'yearly_price' => $t->yearly_price,
                    'one_time_price' => $t->one_time_price,
                    'is_popular' => $t->is_popular,
                    'limits' => $t->limits ?? [],
                ])->values(),
            ],
            'seo' => $module->frontSeoArray(),
        ]);
    }
}
