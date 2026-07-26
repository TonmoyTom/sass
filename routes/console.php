<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('subscriptions:renew')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('subscriptions:remind --days=7')->dailyAt('09:00');
Schedule::command('subscriptions:remind --days=3')->dailyAt('09:00');
Schedule::command('subscriptions:remind --days=1')->dailyAt('09:00');
