<?php

namespace CustomFeature\ClassRanker\DataGrids;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class HashtagDataGrid extends DataGrid
{
    public function prepareQueryBuilder()
    {
        return DB::table('hashtags')->select('hashtags.*');
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'Id',
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => 'Name',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'slug',
            'label'      => 'Slug',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => 'Status',
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_options' => [
                ['label' => 'Active',    'value' => 1],
                ['label' => 'Inactive',  'value' => 0],
            ],
            'sortable' => true,
            'closure'  => function ($row) {
                return $row->status
                    ? '<span class="badge badge-md badge-success">Active</span>'
                    : '<span class="badge badge-md badge-danger">Inactive</span>';
            },
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
            'icon'   => 'icon-edit',
            'title'  => 'Edit',
            'method' => 'GET',
            'url'    => fn ($row) => route('admin.hashtags.edit', $row->id),
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => 'Delete',
            'method' => 'DELETE',
            'url'    => fn ($row) => route('admin.hashtags.delete', $row->id),
        ]);
    }
}