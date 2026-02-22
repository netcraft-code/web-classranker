<?php

namespace CustomFeature\ClassRanker\DataGrids\StudyMaterial;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BookDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('books')
            ->leftJoin('boards', 'books.board_id', '=', 'boards.id')
            ->leftJoin('grades', 'books.grade_id', '=', 'grades.id')
            ->leftJoin('subjects', 'books.subject_id', '=', 'subjects.id')
            ->addSelect(
                'boards.name as board_name',
                'grades.name as grade_name',
                'subjects.name as subject_name',
                'books.id as id',
                'books.code as code',
                'books.title as title',
                'books.avatar as avatar',
                'books.status as status',
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
            'label'      => trans('class_ranker::app.study_materials.books.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'title',
            'label'      => trans('class_ranker::app.study_materials.books.index.datagrid.title'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('class_ranker::app.study_materials.books.index.datagrid.code'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'avatar',
            'label'      => trans('class_ranker::app.study_materials.books.index.datagrid.avatar'),
            'type'       => 'string',
            'closure'    => function ($row) {
                if (! $row->avatar) {
                    return;
                }

                return '<img src="'.Storage::url($row->avatar).'" alt="" width="50" height="50"/>';
            },
        ]);

        $this->addColumn([
            'index'      => 'board_name',
            'label'      => trans('class_ranker::app.study_materials.books.index.datagrid.board-name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'grade_name',
            'label'      => trans('class_ranker::app.study_materials.books.index.datagrid.grade-name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'subject_name',
            'label'      => trans('class_ranker::app.study_materials.books.index.datagrid.subject-name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('class_ranker::app.study_materials.books.index.datagrid.status'),
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
            'title'  => trans('class_ranker::app.study_materials.books.index.datagrid.edit'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.study_materials.books.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => trans('class_ranker::app.study_materials.books.index.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.study_materials.books.delete', $row->id);
            },
        ]);
    }
}
