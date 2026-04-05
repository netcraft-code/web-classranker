<?php

namespace CustomFeature\Question\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \CustomFeature\Question\Models\Question::class,
        \CustomFeature\Question\Models\QuestionItem::class,
    ];
}