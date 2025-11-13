<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule notification outbox processing
Schedule::command('notifications:outbox:process --limit=100')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Notification outbox processing failed');
    })
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::debug('Notification outbox processing completed successfully');
    });
