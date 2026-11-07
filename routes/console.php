<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Flip scheduled content to published as its time arrives.
Schedule::command('cds:publish-due')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Housekeeping so the queue and log tables do not grow without bound.
Schedule::command('queue:prune-failed --hours=336')->weekly();
Schedule::command('activitylog:clean --days=365')->weeklyOn(0, '03:17');
