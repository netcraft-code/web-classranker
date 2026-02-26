<?php

namespace CustomFeature\Note\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class NoteServiceProvider extends ServiceProvider
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