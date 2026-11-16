<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('learnquest:reconcile-payments')->everyFiveMinutes()->withoutOverlapping();

Schedule::command('learnquest:send-subscription-reminders')
    ->dailyAt('08:00')
    ->timezone(config('learnquest.daily_challenges.timezone') ?: config('app.timezone'))
    ->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
