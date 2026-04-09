<?php

namespace CustomFeature\ClassRanker\Repositories;

use CustomFeature\ClassRanker\Contracts\CustomerPlan;
use Webkul\Core\Eloquent\Repository;

class CustomerPlanRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return CustomerPlan::class;
    }
}