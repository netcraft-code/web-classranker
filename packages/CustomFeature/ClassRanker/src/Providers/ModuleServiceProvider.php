<?php

namespace CustomFeature\ClassRanker\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \CustomFeature\ClassRanker\Models\Bookmark::class,
        \CustomFeature\ClassRanker\Models\CustomerPlan::class,
        \CustomFeature\ClassRanker\Models\Plan::class,
        \CustomFeature\ClassRanker\Models\RecentlyViewed::class,
        \CustomFeature\ClassRanker\Models\Hashtag::class,
        \CustomFeature\ClassRanker\Models\Discussion::class,
        \CustomFeature\ClassRanker\Models\DiscussionComment::class,
    ];
}