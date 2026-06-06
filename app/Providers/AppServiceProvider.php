<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Producto;
use App\Models\Configuracion;
use App\Observers\ProductoObserver;
use App\Observers\ConfiguracionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observers para auditoría automática
        Producto::observe(ProductoObserver::class);
        Configuracion::observe(ConfiguracionObserver::class);
    }
}
