<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:create')
    ->dailyAt('01:30')
    ->withoutOverlapping(180)
    ->onOneServer();

Schedule::command('backup:verify')
    ->dailyAt('04:00')
    ->withoutOverlapping(60)
    ->onOneServer();

Schedule::command('notifications:overdue-reports')
    ->dailyAt('08:00')
    ->withoutOverlapping(60)
    ->onOneServer();

Schedule::command('evidence:prune')
    ->dailyAt('02:30')
    ->withoutOverlapping(60)
    ->onOneServer();
