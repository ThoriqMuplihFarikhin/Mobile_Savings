<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('paket:hitung-ulang')->dailyAt('00:10')->withoutOverlapping()->onOneServer();
Schedule::command('paket:kirim-pengingat')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=168')->daily();
