<?php

namespace App\Providers;

use App\Models\Layer;
use App\Models\Layup;
use App\Models\Supplier;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.guest', function ($view) {
            $view->with([
                'supplierCount' => Supplier::count(),
                'layupCount' => Layup::count(),
                'layerCount' => Layer::count(),
            ]);
        });
    }
}
