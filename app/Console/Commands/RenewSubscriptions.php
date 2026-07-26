<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSubscriptionRenewal;
use App\Models\TenantModule;
use Illuminate\Console\Command;

class RenewSubscriptions extends Command
{
    protected $signature = 'subscriptions:renew';

    protected $description = 'Renew due subscriptions and generate recurring sales + commissions';

    public function handle(): int
    {
        $count = 0;

        TenantModule::query()
            ->where('status', 'active')
            ->where('access_type', 'subscription')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($modules) use (&$count) {
                foreach ($modules as $tm) {
                    ProcessSubscriptionRenewal::dispatch($tm->id);
                    $count++;
                }
            });

        $this->info("Dispatched {$count} renewal job(s).");

        return self::SUCCESS;
    }
}
