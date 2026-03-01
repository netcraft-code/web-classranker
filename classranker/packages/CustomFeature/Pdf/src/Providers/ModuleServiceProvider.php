<?php

namespace CustomFeature\Pdf\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \CustomFeature\Pdf\Models\Pdf::class,
    ];
}