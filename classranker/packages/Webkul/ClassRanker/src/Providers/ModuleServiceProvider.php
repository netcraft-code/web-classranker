<?php

namespace Webkul\ClassRanker\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Webkul\ClassRanker\Models\Board::class,
        \Webkul\ClassRanker\Models\Grade::class,
        \Webkul\ClassRanker\Models\Subject::class,
        \Webkul\ClassRanker\Models\Book::class,
        \Webkul\ClassRanker\Models\Chapter::class,
    ];
}
