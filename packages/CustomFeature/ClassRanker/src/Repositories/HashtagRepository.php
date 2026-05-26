<?php

namespace CustomFeature\ClassRanker\Repositories;

use CustomFeature\ClassRanker\Contracts\Hashtag;
use Webkul\Core\Eloquent\Repository;

class HashtagRepository extends Repository
{
    public function model(): string
    {
        return Hashtag::class;
    }

    public function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }
}