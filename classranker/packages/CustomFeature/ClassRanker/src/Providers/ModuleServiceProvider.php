<?php

namespace CustomFeature\ClassRanker\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \CustomFeature\ClassRanker\Models\Board::class,
        \CustomFeature\ClassRanker\Models\Grade::class,
        \CustomFeature\ClassRanker\Models\Subject::class,
        \CustomFeature\ClassRanker\Models\Book::class,
        \CustomFeature\ClassRanker\Models\Chapter::class,
        \CustomFeature\ClassRanker\Models\Question::class,
        \CustomFeature\ClassRanker\Models\QuestionAssignment::class,
        \CustomFeature\ClassRanker\Models\QuestionItem::class,
        \CustomFeature\ClassRanker\Models\QuestionFaq::class,
        \CustomFeature\ClassRanker\Models\Quiz::class,
    ];
}
