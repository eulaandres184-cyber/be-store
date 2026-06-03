<?php
use Illuminate\Support\Facades\Schedule;

/*
 * Scheduler de BE Store
 * Todos los comandos programados se registran acá.
 * El cron del sistema ejecuta: php artisan schedule:run (cada minuto)
 */

// Sincronizar dólar blue todos los días a las 9:00 AM
Schedule::command('bestore:dolar')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/dolar-blue.log'));

// Sincronizar productos desde Google Sheets todos los lunes a las 8:00 AM
Schedule::command('bestore:sync-sheets')
    ->weeklyOn(1, '08:00')  // 1 = lunes
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/sheets-sync.log'));
