<?php

namespace App\Console\Commands;

use Database\Seeders\TenantDatabaseSeeder;
use Illuminate\Console\Command;

class TenantSeed extends Command
{
    protected $signature = 'tenants:seed
                            {--tenants=* : The tenant(s) to seed. Default: all}';

    protected $description = 'Seed tenant database(s).';

    public function handle(): int
    {
        tenancy()->runForMultiple(
            $this->option('tenants'),
            function ($tenant) {
                $this->line("Tenant: {$tenant->getTenantKey()}");

                $seeder = app(TenantDatabaseSeeder::class);

                $seeder->setCommand($this);

                $seeder->run();
            }
        );

        return self::SUCCESS;
    }
}
