<?php

namespace CustomFeature\ClassRanker\Repositories;

use CustomFeature\ClassRanker\Contracts\Bookmark;
use Webkul\Core\Eloquent\Repository;

class BookmarkRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Bookmark::class;
    }
}