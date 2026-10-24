<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:close-finished-events')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('certificates:send-expiry-reminders')
    ->daily()
    ->withoutOverlapping();

Schedule::command('certificates:prune-expired-records')
    ->daily()
    ->withoutOverlapping();
