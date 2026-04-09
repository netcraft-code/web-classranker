<?php

namespace CustomFeature\ClassRanker\Repositories;

use CustomFeature\ClassRanker\Contracts\Plan;
use Webkul\Core\Eloquent\Repository;

class PlanRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Plan::class;
    }

    /**
     * Get all active plans ordered by sort_order then price.
     */
    public function getActivePlans(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->active()->ordered()->get();
    }
 
    /**
     * Find by slug/code.
     */
    public function findByCode(string $code): ?\CustomFeature\ClassRanker\Models\Plan
    {
        return $this->model->where('code', $code)->first();
    }
}