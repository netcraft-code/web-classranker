<?php

namespace CustomFeature\ClassRankerApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAppVersion
{
    public function handle(Request $request, Closure $next)
    {
        $appVersion = $request->header('X-App-Version');

        if (! $appVersion) {
            $storeUrl = core()->getConfigData('class_ranker.settings.app_version.store_url');

            return response()->json([
                'status'       => false,
                'code'         => 'VERSION_HEADER_MISSING',
                'force_update' => true,
                'message' => 'App version could not be detected. Please update the app from the Play Store.',
            ], 400);
        }

        $supportedVersion = core()->getConfigData('class_ranker.settings.app_version.min_version');

        if (version_compare($appVersion, $supportedVersion, '<')) {
            return response()->json([
                'status'       => false,
                'code'         => 'UPDATE_REQUIRED',
                'force_update' => true,
                'message'      => 'Your app is outdated. Please update it from the Play Store.',
                'store_url'    => core()->getConfigData('class_ranker.settings.app_version.store_url'),
                'latest_version' => core()->getConfigData('class_ranker.settings.app_version.latest_version'),
            ], 426);
        }

        return $next($request);
    }
}
