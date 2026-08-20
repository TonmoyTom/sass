<?php

namespace App\Providers;

use App\Enums\UserType;
use App\Models\CompanySetting;
use App\Models\User;
use App\Services\AI\Contracts\AdminToolProvider;
use App\Services\AI\ToolRegistry;
use Illuminate\Foundation\DevCommands;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ToolRegistry::class, function () {
            $registry = new ToolRegistry;
            $registry->registerAdmin(new AdminToolProvider);
            return $registry;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DevCommands::artisan('serve --host=0.0.0.0 --port=8000', 'server');
        DevCommands::artisan('reverb:start --host=0.0.0.0 --port=8081', 'reverb');

        Vite::prefetch(concurrency: 3);

        View::composer('app', function ($view) {
            $favicon = CompanySetting::first()?->favicon_url;
            $view->with('companyFavicon', $favicon ?: '/favicon.png');
        });

        Gate::before(function ($user, $ability) {
            if (! $user instanceof User) {
                return null;
            }

            if ($user->user_type === UserType::SUPER_ADMIN) {
                return true;
            }

            if ($user->user_type === UserType::TENANT_OWNER && function_exists('tenant') && tenant()) {
                return true;
            }

            return null;
        });
    }
}