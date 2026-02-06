<?php

use Illuminate\Support\Facades\Route;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;

Route::group(['middleware' => ['admin', NoCacheMiddleware::class], 'prefix' => config('app.admin_url')], function () {
    /**
     * Sales routes.
     */
    require 'admin-routes.php';
});

Route::get('admin/rose-day', function () {
    return view('class_ranker::dashboard.rose-day');
});
