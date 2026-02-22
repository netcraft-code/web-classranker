<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Core;

use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Core\CountryResource;
use Webkul\Core\Repositories\CountryRepository;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\ShopController;

class CountryController extends ShopController
{
    /**
     * Is resource authorized.
     */
    public function isAuthorized(): bool
    {
        return false;
    }

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return CountryRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return CountryResource::class;
    }

    /**
     * Get country state group listing.
     */
    public function getCountryStateGroups(): \Illuminate\Http\Response
    {
        $resources = core()->groupedStatesByCountries();

        return response(['data' => $resources]);
    }
}
