<?php

namespace CustomFeature\Note\DataGrids;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class NoteDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('notes')
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
            'label'      => 'Id',
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'title',
            'label'      => 'Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'short_title',
            'label'      => 'Short Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'slug',
            'label'      => 'slug',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
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
                return route('admin.study_materials.notes.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => 'Delete',
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.study_materials.notes.delete', $row->id);
            },
        ]);
    }
}
