<?php

namespace CustomFeature\Grade\Providers;

use Illuminate\Support\ServiceProvider;

class GradeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        $this->app->register(ModuleServiceProvider::class);
    }
}