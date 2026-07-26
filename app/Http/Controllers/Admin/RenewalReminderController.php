<?php

namespace App\Http\Controllers\Admin;

use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\TenantModule;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RenewalReminderController extends Controller
{
    public function send(Request $request, $id): RedirectResponse
    {
        $orders = Sale::query()->where('id', $id)->firstOrFail();

        if ($orders) {
            $module = TenantModule::query()
                ->with(['tenant', 'module'])
                ->where('status', 'active')
                ->where('tenant_id', $orders->tenant_id)
                ->where('access_type', 'subscription')
                ->orderBy('created_at', 'desc')
                ->first();

            $price = $module->billing_cycle === 'yearly'
                ? (float) ($module->tier?->yearly_price ?? $module->price_paid)
                : (float) ($module->tier?->monthly_price ?? $module->price_paid);

            $message = "Reminder: Your \"{$module->module?->name}\" subscription will renew on {$module->expires_at->format('d M Y')} for TK".number_format($price, 2).'. Please ensure your payment method is up to date.';
            $centralOwner = User::find($module->tenant->owner_id);

            if ($centralOwner) {
                $tenant = $module->tenant;

                tenancy()->initialize($tenant);
                $tenantUser = TenantUser::where('email', $centralOwner->email)->first();
                if ($tenantUser) {
                    NotificationSent::dispatch(
                        $message,
                        $tenantUser->id,
                        'warning',
                        '/dashboard',
                        auth()->id(),
                        $tenant->id,
                        'admin'
                    );
                }

                tenancy()->end();
            }
        }

        return back()->with('status', 'Notification Sent Successfully');
    }
}
