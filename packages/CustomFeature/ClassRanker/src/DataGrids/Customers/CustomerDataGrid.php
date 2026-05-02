<?php

namespace CustomFeature\ClassRanker\DataGrids\Customers;

use CustomFeature\ClassRanker\Repositories\PlanRepository;
use Webkul\Admin\DataGrids\Customers\CustomerDataGrid as AdminCustomerDataGrid;
use Webkul\DataGrid\DataGrid;

class CustomerDataGrid extends AdminCustomerDataGrid
{
    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'icon'   => 'icon-exit',
            'title'  => trans('admin::app.customers.customers.index.datagrid.login-as-customer'),
            'method' => 'GET',
            'target' => 'blank',
            'url'    => function ($row) {
                return route('admin.customers.customers.login_as_customer', $row->customer_id);
            },
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('customers.customers.delete')) {
            $this->addMassAction([
                'title'  => trans('admin::app.customers.customers.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.customers.customers.mass_delete'),
            ]);
        }

        if (bouncer()->hasPermission('customers.customers.edit')) {
            $this->addMassAction([
                'title'   => trans('admin::app.customers.customers.index.datagrid.update-status'),
                'method'  => 'POST',
                'url'     => route('admin.customers.customers.mass_update'),
                'options' => [
                    [
                        'label' => trans('admin::app.customers.customers.index.datagrid.active'),
                        'value' => 1,
                    ],
                    [
                        'label' => trans('admin::app.customers.customers.index.datagrid.inactive'),
                        'value' => 0,
                    ],
                ],
            ]);
        }

        $planRepository = app(PlanRepository::class);

        $options = $planRepository->getActivePlans()
            ->map(fn ($plan) => [
                'label' => $plan->name,
                'value' => $plan->id,
            ])
            ->values()
            ->toArray();

        $this->addMassAction([
            'title'   => 'Assign Plan',
            'method'  => 'POST',
            'url'     => route('admin.customers.customers.mass_assign_plan'),
            'options' => $options,
        ]);
    }
}
