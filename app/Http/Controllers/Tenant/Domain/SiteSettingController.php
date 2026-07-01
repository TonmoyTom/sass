<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Http\Requests\SeoRequest;
use App\Models\SiteSetting;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingController extends Controller
{
    public function index(): Response
    {
        $settings = SiteSetting::orderBy('page_name')->get();

        return Inertia::render('Tenant/Domain/SiteSettings/Index', [
            'settings' => $settings,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tenant/Domain/SiteSettings/Create');
    }

    public function store(Request $request, FileStorageService $storage): RedirectResponse
    {

        $data = $request->validate([
            'page_name' => 'required|string|max:255',
            'page_key' => [
                'nullable', 'string', 'max:255',
                Rule::unique('tenant.site_settings', 'page_key'),
            ],
            'page_url' => 'required|string|max:255',

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
            'focus_keyword' => 'nullable|string|max:100',
            'sitemap_include' => 'nullable|boolean',
            'sitemap_priority' => 'nullable|numeric|min:0|max:1',
            'sitemap_frequency' => 'nullable|in:always,hourly,daily,weekly,monthly,yearly,never',
        ]);

        $setting = SiteSetting::create([
            'page_name' => $data['page_name'],
            'page_key' => $data['page_key'] ?: Str::slug($data['page_name']),
            'page_url' => $data['page_url'],
        ]);

        $seoData = collect($data)->except(['page_name', 'page_key', 'page_url'])->toArray();

        if ($request->hasFile('og_image')) {
            $seoData['og_image'] = $storage->uploadImage(
                $request->file('og_image'),
                'site_setting',
                ['width' => 400, 'height' => 400, 'quality' => 90]
            );
        }

        $setting->seo()->create($seoData);

        return redirect('/settings/seo')
            ->with('success', 'Page created successfully with SEO');
    }

    public function edit(string $tenant, SiteSetting $setting): Response
    {
        $setting->load('seo');

        return Inertia::render('Tenant/Domain/SiteSettings/Edit', [
            'setting' => $setting,
            'seo' => $setting->seoArray(),
        ]);
    }

    public function updateSeo(SeoRequest $request, string $tenant, SiteSetting $setting, FileStorageService $storage)
    {

        $data = $request->validated();

        if ($request->hasFile('og_image')) {
            $data['og_image'] = $storage->uploadImage(
                $request->file('og_image'),
                'site_setting',
                ['width' => 400, 'height' => 400, 'quality' => 90]
            );
        }

        $setting->seo()->updateOrCreate(
            ['seoable_id' => $setting->id, 'seoable_type' => get_class($setting)],
            $data
        );

        return back()->with('success', 'SEO updated successfully');
    }

    public function destroy(SiteSetting $setting): RedirectResponse
    {
        $setting->delete();

        return redirect()->route('tenant.site-settings.index')
            ->with('success', 'Page deleted successfully');
    }
}
