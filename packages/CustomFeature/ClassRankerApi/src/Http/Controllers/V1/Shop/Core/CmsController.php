<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Core;

use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\ShopController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Core\CmsResource;
use Webkul\CMS\Repositories\PageRepository;

class CmsController extends ShopController
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
        return PageRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return CmsResource::class;
    }
}
