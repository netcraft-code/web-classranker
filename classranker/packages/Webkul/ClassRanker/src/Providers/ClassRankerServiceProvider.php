<?php

namespace Webkul\ClassRanker\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Core\Http\Middleware\PreventRequestsDuringMaintenance;

class ClassRankerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        Route::middleware(['web', PreventRequestsDuringMaintenance::class])->group(__DIR__.'/../Routes/web.php');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'class_ranker');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'class_ranker');

        $this->app->register(ModuleServiceProvider::class);

        // Bind our custom ShipmentRepository
        $this->app->bind(
            \Webkul\Admin\Http\Controllers\DashboardController::class,
            \Webkul\ClassRanker\Http\Controllers\DashboardController::class
        );

        Event::listen('bagisto.admin.layout.head.before', function () {
            return view(
                'class_ranker::style'
            )->render();
        });
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/admin-menu.php', 'menu.admin'
        );
    }
}