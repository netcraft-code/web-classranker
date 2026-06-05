<?php

namespace CustomFeature\ClassRanker\DataGrids;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\DataGrid\DataGrid;

class NotificationDataGrid extends DataGrid
{
    public function prepareQueryBuilder()
    {
        return DB::table('push_notifications');
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'title',
            'label'      => 'Title',
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'text',
            'label'      => 'Message',
            'type'       => 'string',
            'sortable'   => false,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'   => 'image',
            'label'   => 'Image',
            'type'    => 'string',
            'closure' => function ($row) {
                if (! $row->image) {
                    return '-';
                }

                return '<img src="' . Storage::url($row->image) . '" width="45" height="45" style="border-radius:6px; object-fit:cover;" />';
            },
        ]);

        $this->addColumn([
            'index'      => 'redirect_type',
            'label'      => 'Redirect Type',
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'link',
            'label'      => 'Link',
            'type'       => 'string',
            'sortable'   => true,
            'searchable' => true,
        ]);

        $this->addColumn([
            'index'      => 'count',
            'label'      => 'Sent Count',
            'type'       => 'integer',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => 'Created At',
            'type'       => 'date',
            'sortable'   => true,
            'filterable' => true,
            'filterable_type' => 'date_range',
        ]);
    }

    public function prepareActions()
    {
        $this->addAction([
            'icon'   => 'icon-edit',
            'title'  => 'Edit',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.notifications.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-exit',
            'title'  => 'Send',
            'method' => 'POST',
            'url'    => function ($row) {
                return route('admin.notifications.send_to_all', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => 'Delete',
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.notifications.delete', $row->id);
            },
        ]);
    }
}