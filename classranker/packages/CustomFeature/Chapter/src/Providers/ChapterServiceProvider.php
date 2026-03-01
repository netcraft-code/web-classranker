<?php

namespace CustomFeature\Chapter\Providers;

use Illuminate\Support\ServiceProvider;

class ChapterServiceProvider extends ServiceProvider
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