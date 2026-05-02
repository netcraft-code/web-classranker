<?php

namespace CustomFeature\ClassRanker\DataGrids\Customers;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class CustomerPlanDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('customer_plans')
            ->join('customers', 'customer_plans.customer_id', '=', 'customers.id')
            ->join('plans', 'customer_plans.plan_id', '=', 'plans.id')
            ->select(
                'customer_plans.id',
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                'plans.name as plan_name',
                'plans.code as plan_code',
                'customer_plans.amount_paid',
                'customer_plans.currency',
                'customer_plans.status',
                'customer_plans.starts_at',
                'customer_plans.expires_at',
                'customer_plans.created_at'
            );

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'integer',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'first_name',
            'label'      => 'Customer',
            'type'       => 'string',
            'closure'    => function ($row) {
                return $row->first_name . ' ' . $row->last_name;
            },
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => 'Email',
            'type'       => 'string',
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'plan_name',
            'label'      => 'Plan',
            'type'       => 'string',
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'plan_code',
            'label'      => 'Code',
            'type'       => 'string',
        ]);

        $this->addColumn([
            'index'      => 'amount_paid',
            'label'      => 'Amount',
            'type'       => 'string',
        ]);

        $this->addColumn([
            'index'      => 'currency',
            'label'      => 'Currency',
            'type'       => 'string',
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => 'Status',
            'type'       => 'string',
            'filterable' => true,
            'filterable_options' => [
                ['label' => 'Pending',   'value' => 'pending'],
                ['label' => 'Active',    'value' => 'active'],
                ['label' => 'Expired',   'value' => 'expired'],
                ['label' => 'Cancelled', 'value' => 'cancelled'],
            ],
            'sortable'   => true,

            'closure' => function ($row) {

                return match ($row->status) {
                    'pending'   => '<span class="badge badge-warning">Pending</span>',
                    'active'    => '<span class="badge badge-success">Active</span>',
                    'expired'   => '<span class="badge badge-dark">Expired</span>',
                    'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
                    default     => '<span class="badge badge-secondary">Unknown</span>',
                };
            },
        ]);

        $this->addColumn([
            'index'      => 'starts_at',
            'label'      => 'Start Date',
            'type'       => 'datetime',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'expires_at',
            'label'      => 'Expiry Date',
            'type'       => 'datetime',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => 'Created At',
            'type'            => 'date',
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
        ]);
    }

    public function prepareActions()
    {   
        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => 'Cancel Plan',
            'method' => 'POST',
             'url'    => function ($row) {
                return route('admin.plans.customer_plans.cancel_plan', $row->id);
            },
        ]);
    }
}
