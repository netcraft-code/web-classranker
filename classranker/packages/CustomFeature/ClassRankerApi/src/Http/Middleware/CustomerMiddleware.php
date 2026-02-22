<?php

namespace CustomFeature\ClassRankerApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        /**
         * This is for session based authentication.
         */
        if (EnsureFrontendRequestsAreStateful::fromFrontend($request)) {
            if (! auth('customer')->user()) {
                return response([
                    'message' => 'Not Authorized',
                ], 401);
            }

            return $next($request);
        }

        /**
         * This is for token based authentication.
         */
        if ($request->user()?->tokenCan('role:customer')) {
            return $next($request);
        }

        return response([
            'message' => 'Not Authorized',
        ], 401);
    }
}
