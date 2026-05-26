<?php

namespace CustomFeature\ClassRankerApi\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Core\Exceptions\Handler as BaseHandler;
use CustomFeature\ClassRankerApi\Exceptions\Handler;

class ClassRankerApiServiceProvider extends ServiceProvider
{
    /**
     * Register your middleware aliases here.
     *
     * @var array
     */
    protected $middlewareAliases = [
        'etag'             => \CustomFeature\ClassRankerApi\Http\Middleware\ETagMiddleware::class,
        'sanctum.customer' => \CustomFeature\ClassRankerApi\Http\Middleware\CustomerMiddleware::class,
        'sanctum.locale'   => \CustomFeature\ClassRankerApi\Http\Middleware\LocaleMiddleware::class,
        'sanctum.currency' => \CustomFeature\ClassRankerApi\Http\Middleware\CurrencyMiddleware::class,
        'app.version'      => \CustomFeature\ClassRankerApi\Http\Middleware\CheckAppVersion::class,
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'class_ranker_api');

        $this->activateMiddlewareAliases();

        $this->app->bind(BaseHandler::class, Handler::class);

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'class_ranker_api');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mapApiRoutes();
    }

    /**
     * Activate middleware aliases.
     *
     * @return void
     */
    protected function activateMiddlewareAliases()
    {
        collect($this->middlewareAliases)->each(function ($className, $alias) {
            $this->app['router']->aliasMiddleware($alias, $className);
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware(['api', 'etag', 'app.version'])
            ->group(__DIR__.'/../Routes/api.php');
    }
}
