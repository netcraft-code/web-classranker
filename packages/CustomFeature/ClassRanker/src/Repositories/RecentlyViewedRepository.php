<?php

namespace CustomFeature\ClassRanker\Repositories;

use CustomFeature\ClassRanker\Models\RecentlyViewed;
use Webkul\Core\Eloquent\Repository;

class RecentlyViewedRepository extends Repository
{
    public function model(): string
    {
        return RecentlyViewed::class;
    }
}