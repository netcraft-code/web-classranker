<?php

namespace CustomFeature\ClassRanker\Http\Controllers;

use Webkul\Admin\Http\Controllers\DashboardController as AdminDashboardController;

class DashboardController extends AdminDashboardController
{
    /**
     * Dashboard page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return view('class_ranker::dashboard.index')->with([
            'startDate' => $this->dashboardHelper->getStartDate(),
            'endDate'   => $this->dashboardHelper->getEndDate(),
        ]);
    }
}
