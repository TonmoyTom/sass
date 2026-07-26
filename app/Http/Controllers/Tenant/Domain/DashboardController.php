<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $settings = CompanySetting::current();
        $user = auth()->user();
       

        // ── Tenant details ──
        $tenant = [
            'name' => $settings->company_name ?? 'Workspace',
            'code' => tenant('id'),
            'domain' => request()->getHost(),
            'logo' => $settings->logo_url ?? null,
            'status' => $settings->status ?? 'active',
            'plan' => $settings->plan ?? 'Free',
            'renews_at' => $settings->renews_at ?? null,
            'created_at' => optional($settings->created_at)->format('d M, Y'),
            'region' => $settings->region ?? 'BD',
        ];

        // ── Owner profile ──
        $owner = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? null,
            'role' => $user->role ?? 'Owner',
        ];

        // ── Sales overview ── (adjust to your actual Order model)
        $sales = [
            'revenue' => 0,
            'change_pct' => 0,
            'total_orders' => 0,
            'avg_order_value' => 0,
            'pending' => 0,
            'chart' => [],
        ];

        // ── Recent orders ── (adjust to your actual Order model)
        $recentOrders = [];

        // ── Purchased modules ── (adjust to your actual Module model)
        $modules = [];

        return Inertia::render('Tenant/Domain/Dashboard', [
            'tenant' => $tenant,
            'owner' => $owner,
            'sales' => $sales,
            'recentOrders' => $recentOrders,
            'modules' => $modules,
        ]);
    }
}
