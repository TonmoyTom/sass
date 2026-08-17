<?php

namespace Modules\LMS\Providers;

use App\Services\AI\ToolRegistry;
use Illuminate\Console\Scheduling\Schedule;
use Inertia\Inertia;
use Modules\LMS\Classes\AI\LmsToolProvider;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Support\ModuleServiceProvider;

class LMSServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'LMS';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'lms';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }

    public function boot(): void
    {
        parent::boot();

        // Inertia root view — module-e alada view lagbe na, main app-er 'app' view-i use hobe
        Inertia::setRootView('app');
        if (Module::has('LMS') && Module::find('LMS')?->isEnabled()) {
            app(ToolRegistry::class)
                ->register(new LmsToolProvider);
        }
    }
}
