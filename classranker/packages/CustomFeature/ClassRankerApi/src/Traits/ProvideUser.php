<?php

namespace CustomFeature\ClassRankerApi\Traits;

use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

trait ProvideUser
{
    /**
     * Set default auth driver for shop.
     *
     * @return void
     */
    public function setShopAuthDriver(Request $request)
    {
        if (EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
            auth()->setDefaultDriver('customer');
        }
    }

    /**
     * Resolve shop user.
     *
     * @return \Webkul\Customer\Contracts\Customer
     */
    public function resolveShopUser(Request $request)
    {
        if (EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
            return auth('customer')->user();
        }

        return $request->user();
    }
}
