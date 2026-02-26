<?php

use Illuminate\Support\Facades\Route;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Core\CountryController;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Core\CountryStateController;

/**
 * Country routes.
 */
Route::controller(CountryController::class)->prefix('countries')->group(function () {
    Route::get('', 'allResources');

    Route::get('{id}', 'getResource');

    Route::get('states/groups', 'getCountryStateGroups');

});

Route::controller(CountryStateController::class)->prefix('countries-states')->group(function () {
    Route::get('', 'allResources');
});
