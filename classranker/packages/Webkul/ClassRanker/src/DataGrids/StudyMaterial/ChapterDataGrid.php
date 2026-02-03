<?php

namespace Webkul\ClassRanker\DataGrids\StudyMaterial;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChapterDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('chapters')
            ->select('*');

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
            'label'      => trans('class_ranker::app.study_materials.chapters.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'title',
            'label'      => trans('class_ranker::app.study_materials.chapters.index.datagrid.title'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('class_ranker::app.study_materials.chapters.index.datagrid.code'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'avatar',
            'label'      => trans('class_ranker::app.study_materials.chapters.index.datagrid.avatar'),
            'type'       => 'string',
            'closure'    => function ($row) {
                if (! $row->avatar) {
                    return;
                }

                return '<img src="'.Storage::url($row->avatar).'" alt="" width="50" height="50"/>';
            },
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('class_ranker::app.study_materials.chapters.index.datagrid.status'),
            'type'       => 'boolean',
            'filterable' => true,
            'filterable_options' => [
                [
                    'label' => trans('admin::app.catalog.categories.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('admin::app.catalog.categories.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($value) {
                if ($value->status) {
                    return '<span class="badge badge-md badge-success">'.trans('admin::app.catalog.categories.index.datagrid.active').'</span>';
                }

                return '<span class="badge badge-md badge-danger">'.trans('admin::app.catalog.categories.index.datagrid.inactive').'</span>';
            },
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'icon'   => 'icon-edit',
            'title'  => trans('class_ranker::app.study_materials.chapters.index.datagrid.edit'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.study_materials.chapters.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => trans('class_ranker::app.study_materials.chapters.index.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.study_materials.chapters.delete', $row->id);
            },
        ]);
    }
}
