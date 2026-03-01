<?php

namespace CustomFeature\Subject\DataGrids;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubjectDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('subjects')
            ->leftJoin('boards', 'subjects.board_id', '=', 'boards.id')
            ->leftJoin('grades', 'subjects.grade_id', '=', 'grades.id')
            ->addSelect(
                'boards.code as board_code',
                'boards.name as board_name',
                'boards.title as board_title',
                'grades.code as grade_code',
                'grades.name as grade_name',
                'grades.title as grade_title',
                'subjects.id as id',
                'subjects.code as code',
                'subjects.name as name',
                'subjects.avatar as avatar',
                'subjects.status as status',
                'subjects.created_at as created_at',
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
            'index'      => 'code',
            'label'      => 'Code',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'avatar',
            'label'      => 'Icon',
            'type'       => 'string',
            'closure'    => function ($row) {
                if (! $row->avatar) {
                    return;
                }

                return '<img src="'.Storage::url($row->avatar).'" alt="" width="80" height="80"/>';
            },
        ]);

        $this->addColumn([
            'index'      => 'board_name',
            'label'      => 'Board Name',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'board_code',
            'label'      => 'Board Code',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'board_title',
            'label'      => 'Board Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'grade_name',
            'label'      => 'Grade Name',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'grade_code',
            'label'      => 'Grade Code',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'grade_title',
            'label'      => 'Grade Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => 'Status',
            'type'       => 'boolean',
            'filterable' => true,
            'filterable_options' => [
                [
                    'label' => 'Active',
                    'value' => 1,
                ],
                [
                    'label' => 'In Active',
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($value) {
                if ($value->status) {
                    return '<span class="badge badge-md badge-success">Active</span>';
                }

                return '<span class="badge badge-md badge-danger">In Active</span>';
            },
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => 'Created At',
            'type'            => 'date',
            'searchable'      => true,
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
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
            'title'  => 'Edit',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.study_materials.subjects.subjects.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => 'Delete',
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.study_materials.subjects.subjects.delete', $row->id);
            },
        ]);
    }
}
