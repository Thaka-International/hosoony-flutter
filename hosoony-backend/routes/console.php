<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule commands
Schedule::command('session:reminders')->everyMinute();
Schedule::command('announcements:dispatch-daily')->dailyAt('08:00');
Schedule::command('announcements:dispatch-weekly')->weeklyOn(0, '08:00'); // Sunday at 08:00
Schedule::command('payment:reminders')->hourly();
Schedule::command('daily:close-logs')->dailyAt('23:59');
Schedule::command('points:award-daily')->dailyAt('00:00');
Schedule::command('badges:weekly')->weeklyOn(0, '20:00'); // Sunday at 20:00
Schedule::command('badges:monthly')->monthlyOn(null, '20:00'); // Last day of month at 20:00

// Companions auto-publish - يتم تشغيله قبل 23:59 حسب الإعدادات
Schedule::command('companions:autopublish')->dailyAt(config('quran_lms.companions.default_publish_time', '23:59'));
