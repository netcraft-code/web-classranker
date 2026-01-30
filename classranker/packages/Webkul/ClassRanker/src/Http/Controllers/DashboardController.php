<?php

namespace Webkul\ClassRanker\Http\Controllers;

use Webkul\Admin\Helpers\Dashboard;
use Webkul\Admin\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(protected Dashboard $dashboardHelper) {}

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
