<?php

namespace App\Providers;

use App\Enums\UserType;
use App\Models\CompanySetting;
use App\Models\User;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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
