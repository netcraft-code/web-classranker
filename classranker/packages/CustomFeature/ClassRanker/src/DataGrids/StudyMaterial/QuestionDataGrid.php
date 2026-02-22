<?php

namespace CustomFeature\ClassRanker\DataGrids\StudyMaterial;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class QuestionDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('questions')
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
            'label'      => trans('class_ranker::app.study_materials.questions.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'top_description',
            'label'      => trans('class_ranker::app.study_materials.chapters.index.datagrid.title'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'closure'    => function ($row) {
                return '<span>' . $row->top_description . '</span>';
            },
        ]);

        $this->addColumn([
            'index'      => 'bottom_description',
            'label'      => trans('class_ranker::app.study_materials.chapters.index.datagrid.title'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'closure'    => function ($row) {
                return '<span>' . $row->bottom_description . '</span>';
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
            'title'  => trans('class_ranker::app.study_materials.questions.index.datagrid.edit'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.study_materials.questions.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => trans('class_ranker::app.study_materials.questions.index.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.study_materials.questions.delete', $row->id);
            },
        ]);
    }
}
