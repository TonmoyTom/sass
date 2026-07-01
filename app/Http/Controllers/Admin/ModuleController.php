<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreModuleRequest;
use App\Http\Requests\Admin\UpdateModuleRequest;
use App\Http\Requests\SeoRequest;
use App\Models\ModulePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:modules.view')->only(['index', 'show']);
        $this->middleware('can:modules.create')->only(['create', 'store']);
        $this->middleware('can:modules.edit')->only(['edit', 'update', 'updateSeo']);
        $this->middleware('can:modules.delete')->only(['destroy']);
    }

    public function index(): Response
    {
        $modules = ModulePackage::query()
            ->with('tiers')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'alias' => $m->alias,
                'icon' => $m->icon,
                'category' => $m->module_category,
                'pricing_type' => $m->pricing_type,
                'commission_rate' => $m->commission_rate,
                'is_active' => $m->is_active,
                'is_core' => $m->is_core,
                'tiers_count' => $m->tiers->count(),
                'starting_price' => $m->tiers->where('is_active', true)->min('monthly_price'),
            ]);

        return Inertia::render('Admin/Modules/Index', ['modules' => $modules]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Modules/Create', $this->formOptions());
    }

    public function store(StoreModuleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $module = ModulePackage::create([
                'name' => $data['name'],
                'alias' => $data['alias'] ?: Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'icon' => $data['icon'] ?? null,
                'version' => $data['version'] ?? '1.0.0',
                'pricing_type' => $data['pricing_type'],
                'commission_rate' => $data['commission_rate'],
                'module_category' => $data['module_category'],
                'is_active' => $data['is_active'] ?? true,
                'is_core' => $data['is_core'] ?? false,
                'sort_order' => $data['sort_order'] ?? 0,
                'features' => $this->splitLines($data['features'] ?? null),
            ]);

            $this->syncTiers($module, $data['tiers'] ?? []);
        });

        return redirect()->route('admin.modules.index')
            ->with('status', 'Module created successfully');
    }

    public function edit(ModulePackage $module): Response
    {

        $module->load('tiers', 'seo');

        return Inertia::render('Admin/Modules/Edit', array_merge(
            [
                'seo' => $module->seoArray(),
                'module' => array_merge($module->toArray(), [
                    'features' => is_array($module->features) ? implode("\n", $module->features) : '',
                    'tiers' => $module->tiers->map(fn ($t) => [
                        'id' => $t->id,
                        'name' => $t->name,
                        'slug' => $t->slug,
                        'limits' => $t->limits ?? [],
                        'monthly_price' => $t->monthly_price,
                        'yearly_price' => $t->yearly_price,
                        'one_time_price' => $t->one_time_price,
                        'is_active' => $t->is_active,
                        'is_popular' => $t->is_popular,
                        'sort_order' => $t->sort_order,
                    ])->values()->all(),
                ]),
            ],
            $this->formOptions()
        ));
    }

    public function show(ModulePackage $module): Response
    {
        $module->load('tiers');

        return Inertia::render('Admin/Modules/Show', [

            'module' => array_merge($module->toArray(), [
                'tiers' => $module->tiers->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'limits' => $t->limits ?? [],
                    'monthly_price' => $t->monthly_price,
                    'yearly_price' => $t->yearly_price,
                    'is_active' => $t->is_active,
                    'is_popular' => $t->is_popular,
                ])->values()->all(),
            ]),
        ]);
    }

    public function update(UpdateModuleRequest $request, ModulePackage $module): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $module) {
            $module->update([
                'name' => $data['name'],
                'alias' => $data['alias'] ?: Str::slug($data['name']),
                'description' => $data['description'] ?? null,
                'icon' => $data['icon'] ?? null,
                'version' => $data['version'] ?? '1.0.0',
                'pricing_type' => $data['pricing_type'],
                'commission_rate' => $data['commission_rate'],
                'module_category' => $data['module_category'],
                'is_active' => $data['is_active'] ?? true,
                'is_core' => $data['is_core'] ?? false,
                'sort_order' => $data['sort_order'] ?? 0,
                'features' => $this->splitLines($data['features'] ?? null),
            ]);

            $this->syncTiers($module, $data['tiers'] ?? []);
        });

        return redirect()->route('admin.modules.index')
            ->with('status', 'Module updated successfully');
    }

    public function destroy(ModulePackage $module): RedirectResponse
    {
        // if ($module->is_core) {
        //     return back()->with('error', 'Core module delete kora jabe na.');
        // }

        $module->delete(); // tiers cascade (FK)

        return redirect()->route('admin.modules.index')
            ->with('status', 'Module deleted successfully');
    }

    public function updateSeo(SeoRequest $request, ModulePackage $module)
    {
        $data = $request->validated();

        if ($request->hasFile('og_image')) {
            $data['og_image'] = $request->file('og_image')->store('seo', 'public');
        }

        $module->seo()->updateOrCreate(
            ['seoable_id' => $module->id, 'seoable_type' => get_class($module)],
            $data
        );

        return back()->with('success', 'SEO updated successfully');
    }

    // tier sync — notun add, existing update, baad-dewa delete
    private function syncTiers(ModulePackage $module, array $tiers): void
    {
        $keepIds = [];

        foreach ($tiers as $i => $tier) {
            $limits = collect($tier['limits'] ?? [])
                ->filter(fn ($v, $k) => $k !== '' && $v !== '' && $v !== null)
                ->map(fn ($v) => is_numeric($v) ? (int) $v : $v)
                ->all();

            $payload = [
                'name' => $tier['name'],
                'slug' => $tier['slug'] ?: Str::slug($tier['name']),
                'limits' => $limits,
                'monthly_price' => $tier['monthly_price'] ?? 0,
                'yearly_price' => $tier['yearly_price'] ?? 0,
                'one_time_price' => $tier['one_time_price'] ?? null,
                'is_active' => $tier['is_active'] ?? true,
                'is_popular' => $tier['is_popular'] ?? false,
                'sort_order' => $i,
            ];

            if (! empty($tier['id'])) {
                $module->tiers()->where('id', $tier['id'])->update($payload);
                $keepIds[] = $tier['id'];
            } else {
                $new = $module->tiers()->create($payload);
                $keepIds[] = $new->id;
            }
        }

        // form-e na thaka tier delete
        $module->tiers()->whereNotIn('id', $keepIds)->delete();
    }

    private function formOptions(): array
    {
        return [
            'pricingTypes' => [
                ['value' => 'subscription', 'label' => 'Subscription'],
                ['value' => 'one_time', 'label' => 'One Time'],
            ],
            'categories' => [
                ['value' => 'core', 'label' => 'Core'],
                ['value' => 'addon', 'label' => 'Addon'],
                ['value' => 'utility', 'label' => 'Utility'],
            ],
        ];
    }

    private function splitLines(?string $text): array
    {
        if (! $text) {
            return [];
        }

        return collect(explode("\n", $text))
            ->map(fn ($l) => trim($l))
            ->filter()
            ->values()
            ->all();
    }
}
