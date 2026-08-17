<?php

// app/Services/AI/AdminToolProvider.php

namespace App\Services\AI\Contracts;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\Tenant;

class AdminToolProvider
{
    public function tools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_todays_sales',
                    'description' => 'আজকে মোট কতগুলো অর্ডার/সেল হয়েছে এবং মোট এমাউন্ট কত',
                    'parameters' => ['type' => 'object', 'properties' => new \stdClass],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_new_tenants_count',
                    'description' => 'নির্দিষ্ট সময়সীমায় কতগুলো নতুন tenant তৈরি হয়েছে',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'period' => [
                                'type' => 'string',
                                'enum' => ['today', 'this_week', 'this_month', 'this_year'],
                                'description' => 'ডিফল্ট: today',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_new_sellers_count',
                    'description' => 'নির্দিষ্ট সময়সীমায় কতজন নতুন seller যোগ হয়েছে (অ্যাপ্রুভড/পেন্ডিং সহ)',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'period' => [
                                'type' => 'string',
                                'enum' => ['today', 'this_week', 'this_month', 'this_year'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_platform_overview',
                    'description' => 'সম্পূর্ণ প্ল্যাটফর্মের সারসংক্ষেপ — মোট tenant, seller, active subscription, এই মাসের রেভিনিউ',
                    'parameters' => ['type' => 'object', 'properties' => new \stdClass],
                ],
            ],
        ];
    }

    public function execute(string $toolName, array $args): mixed
    {
        return match ($toolName) {
            'get_todays_sales' => [
                'count' => Sale::whereDate('sold_at', today())->count(),
                'total_amount' => Sale::whereDate('sold_at', today())->where('status', 'completed')->sum('amount'),
            ],

            'get_new_tenants_count' => $this->countByPeriod(Tenant::query(), $args['period'] ?? 'today'),

            'get_new_sellers_count' => [
                'total' => $this->countByPeriod(Seller::query(), $args['period'] ?? 'today'),
                'approved' => $this->countByPeriod(Seller::where('status', 'active'), $args['period'] ?? 'today'),
                'pending' => $this->countByPeriod(Seller::where('status', 'pending'), $args['period'] ?? 'today'),
            ],

            'get_platform_overview' => [
                'total_tenants' => Tenant::count(),
                'active_tenants' => Tenant::where('status', 'active')->count(),
                'total_sellers' => Seller::count(),
                'this_month_revenue' => Sale::whereMonth('sold_at', now()->month)
                    ->where('status', 'completed')
                    ->sum('amount'),
            ],

            default => ['error' => 'Unknown admin tool'],
        };
    }

    protected function countByPeriod($query, string $period): int
    {
        $from = match ($period) {
            'today' => today(),
            'this_week' => now()->startOfWeek(),
            'this_month' => now()->startOfMonth(),
            'this_year' => now()->startOfYear(),
            default => today(),
        };

        return $query->where('created_at', '>=', $from)->count();
    }
}
