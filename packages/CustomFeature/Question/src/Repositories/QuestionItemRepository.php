<?php

namespace CustomFeature\Question\Repositories;

use Webkul\Core\Eloquent\Repository;
use CustomFeature\Question\Contracts\QuestionItem;

class QuestionItemRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return QuestionItem::class;
    }
}
