<?php

namespace App\Providers;

use App\Models\Ingreso;
use App\Models\Orden;
use App\Observers\IngresoObserver;
use App\Observers\OrdenObserver;
use Illuminate\Support\ServiceProvider;

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
        // Notificaciones automáticas (ingresos, cambios de estado, asignaciones)
        Ingreso::observe(IngresoObserver::class);
        Orden::observe(OrdenObserver::class);
    }
}
