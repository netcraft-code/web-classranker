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
    ];
}