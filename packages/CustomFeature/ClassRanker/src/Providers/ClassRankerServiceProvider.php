<?php

namespace CustomFeature\ClassRanker\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Core\Http\Middleware\PreventRequestsDuringMaintenance;
use Webkul\Customer\Contracts\Customer as CustomerContract;
use CustomFeature\ClassRanker\Models\Customer\Customer as CustomCustomer;

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

        $this->app->bind(
            \Webkul\Admin\Http\Controllers\DashboardController::class,
            \CustomFeature\ClassRanker\Http\Controllers\Dashboard\DashboardController::class
        );

        $this->app->bind(CustomerContract::class, CustomCustomer::class);

        $this->app->register(ModuleServiceProvider::class);
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