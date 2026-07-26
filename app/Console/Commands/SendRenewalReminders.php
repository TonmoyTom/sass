<?php

namespace App\Console\Commands;

use App\Events\NotificationSent;
use App\Models\TenantModule;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:send-renewal-reminders {--days=7} {--sender=}')]
#[Description('Send subscription renewal reminder notifications to tenant owners')]
class SendRenewalReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $targetDate = now()->addDays($days)->toDateString();
        $senderId = $this->option('sender');

        $count = 0;
        $skipped = 0;

        TenantModule::query()
            ->where('status', 'active')
            ->where('access_type', 'subscription')
            ->whereNotNull('expires_at')
            // ->whereDate('expires_at', $targetDate)   // exact oi din e expire hocche emon gulo
            ->with(['tenant', 'module'])
            ->chunkById(100, function ($modules) use (&$count, &$skipped, $senderId) {
                foreach ($modules as $tm) {
                    $tenant = $tm->tenant;

                    if (! $tenant || ! $tenant->owner_id) {
                        $skipped++;

                        continue;
                    }

                    // central owner user (email lagbe tenant DB-te match korar jonno)
                    $centralOwner = User::find($tenant->owner_id);

                    if (! $centralOwner) {
                        $this->warn("Central owner (id: {$tenant->owner_id}) not found for tenant {$tenant->id}, skipping.");
                        $skipped++;

                        continue;
                    }

                    $price = $tm->billing_cycle === 'yearly'
                        ? (float) ($tm->tier?->yearly_price ?? $tm->price_paid)
                        : (float) ($tm->tier?->monthly_price ?? $tm->price_paid);

                    $message = "Reminder: Your \"{$tm->module?->name}\" subscription will renew on {$tm->expires_at->format('d M Y')} for TK".number_format($price, 2).'. Please ensure your payment method is up to date.';

                    // tenant DB context e dhukte hobe TenantUser lookup ebong broadcast er jonno
                    tenancy()->initialize($tenant);

                    $tenantUser = TenantUser::where('email', $centralOwner->email)->first();

                    if ($tenantUser) {
                        NotificationSent::dispatch(
                            $message,
                            $tenantUser->id,
                            'warning',
                            '/dashboard',
                            $senderId ? (int) $senderId : null,
                            $tenant->id
                        );

                        $count++;
                    } else {
                        $this->warn("Tenant user with email {$centralOwner->email} not found in tenant {$tenant->id}, skipping.");
                        $skipped++;
                    }

                    tenancy()->end();public function handle()
    {
        $days = (int) $this->option('days');
        $targetDate = now()->addDays($days)->toDateString();
        $senderId = $this->option('sender');

        $count = 0;
        $skipped = 0;

        TenantModule::query()
            ->where('status', 'active')
            ->where('access_type', 'subscription')
            ->whereNotNull('expires_at')
            // ->whereDate('expires_at', $targetDate)   // exact oi din e expire hocche emon gulo
            ->with(['tenant', 'module'])
            ->chunkById(100, function ($modules) use (&$count, &$skipped, $senderId) {
                foreach ($modules as $tm) {
                    $tenant = $tm->tenant;

                    if (! $tenant || ! $tenant->owner_id) {
                        $skipped++;

                        continue;
                    }

                    // central owner user (email lagbe tenant DB-te match korar jonno)
                    $centralOwner = User::find($tenant->owner_id);

                    if (! $centralOwner) {
                        $this->warn("Central owner (id: {$tenant->owner_id}) not found for tenant {$tenant->id}, skipping.");
                        $skipped++;

                        continue;
                    }

                    $price = $tm->billing_cycle === 'yearly'
                        ? (float) ($tm->tier?->yearly_price ?? $tm->price_paid)
                        : (float) ($tm->tier?->monthly_price ?? $tm->price_paid);

                    $message = "Reminder: Your \"{$tm->module?->name}\" subscription will renew on {$tm->expires_at->format('d M Y')} for TK".number_format($price, 2).'. Please ensure your payment method is up to date.';

                    // tenant DB context e dhukte hobe TenantUser lookup ebong broadcast er jonno
                    tenancy()->initialize($tenant);

                    $tenantUser = TenantUser::where('email', $centralOwner->email)->first();

                    if ($tenantUser) {
                        NotificationSent::dispatch(
                            $message,
                            $tenantUser->id,
                            'warning',
                            '/dashboard',
                            $senderId ? (int) $senderId : null,
                            $tenant->id,
                           
                        );

                        $count++;
                    } else {
                        $this->warn("Tenant user with email {$centralOwner->email} not found in tenant {$tenant->id}, skipping.");
                        $skipped++;
                    }

                    tenancy()->end();
                }
            });

        $this->info("Sent {$count} renewal reminder(s) for subscriptions expiring in {$days} day(s). Skipped: {$skipped}.");

        return self::SUCCESS;
    }
                }
            });

        $this->info("Sent {$count} renewal reminder(s) for subscriptions expiring in {$days} day(s). Skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
