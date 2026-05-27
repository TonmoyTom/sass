<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin Dashboard Controller
 *
 * Main dashboard for super admins.
 * Shows platform-wide stats, recent activity, etc.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = $this->getStats();

        return $this->respond(
            page: 'Admin/Dashboard',
            data: [
                'stats' => $stats,
                'recentTenants' => $this->getRecentTenants(),
                'topSellers' => $this->getTopSellers(),
                'recentActivity' => $this->getRecentActivity(),
            ],
        );
    }

    /**
     * Platform-wide statistics.
     */
    protected function getStats(): array
    {
        return [
            'total_tenants' => $this->safeCount('tenants'),
            'active_tenants' => $this->safeCount('tenants', ['status' => 'active']),
            'trial_tenants' => $this->safeCount('tenants', ['status' => 'trial']),
            'total_sellers' => User::sellers()->count(),
            'active_sellers' => User::sellers()->active()->count(),
            'pending_sellers' => User::sellers()->where('status', 'pending')->count(),
            'monthly_revenue' => $this->getMonthlyRevenue(),
            'pending_withdrawals' => $this->getPendingWithdrawals(),
        ];
    }

    /**
     * Recent tenants (last 10).
     */
    protected function getRecentTenants()
    {
        try {
            return \App\Models\Tenant::query()
                ->with('owner')
                ->latest()
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Top performing sellers.
     */
    protected function getTopSellers()
    {
        return User::sellers()
            ->active()
            ->limit(5)
            ->get(['id', 'name', 'email', 'avatar', 'last_login_at']);
    }

    /**
     * Recent platform activity (placeholder).
     */
    protected function getRecentActivity(): array
    {
        return [
            // Will be populated from audit_logs table later
        ];
    }

    /**
     * Safe count helper - returns 0 if table doesn't exist yet.
     */
    protected function safeCount(string $table, array $where = []): int
    {
        try {
            $query = \DB::table($table);
            foreach ($where as $col => $val) {
                $query->where($col, $val);
            }
            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Monthly revenue (placeholder).
     */
    protected function getMonthlyRevenue(): float
    {
        try {
            return (float) \DB::table('payments')
                ->where('status', 'success')
                ->whereMonth('created_at', now()->month)
                ->sum('amount');
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Pending withdrawal amount (placeholder).
     */
    protected function getPendingWithdrawals(): float
    {
        try {
            return (float) \DB::table('withdrawal_requests')
                ->where('status', 'pending')
                ->sum('amount');
        } catch (\Exception $e) {
            return 0;
        }
    }
}
