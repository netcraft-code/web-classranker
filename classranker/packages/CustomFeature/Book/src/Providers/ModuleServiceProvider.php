<?php

namespace CustomFeature\Book\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \CustomFeature\Book\Models\Book::class,
    ];
}