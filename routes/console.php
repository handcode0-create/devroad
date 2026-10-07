<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rappels d'apprentissage : vérifiés chaque minute (nécessite `php artisan schedule:work` ou un cron `schedule:run`).
\Illuminate\Support\Facades\Schedule::command('devroad:send-reminders')->everyMinute()->withoutOverlapping();
