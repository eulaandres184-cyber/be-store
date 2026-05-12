<?php
use Illuminate\Support\Facades\Schedule;

// Sincronizar dólar blue todos los días a las 9:00 AM
Schedule::command('bestore:dolar')->dailyAt('09:00');
