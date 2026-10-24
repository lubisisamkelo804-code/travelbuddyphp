<?php

namespace App\Providers;

use App\Services\CurrencyService;
use App\Services\WeatherService;
use App\Support\TravelRail;
use App\View\Composers\RailComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WeatherService::class);
        $this->app->singleton(CurrencyService::class);
        $this->app->singleton(TravelRail::class);
    }

    public function boot(): void
    {
        View::composer('partials.sidebar', RailComposer::class);
        Paginator::useBootstrapFour();
    }
}