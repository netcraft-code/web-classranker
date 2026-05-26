<?php

namespace CustomFeature\ClassRanker\DataGrids;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class DiscussionDataGrid extends DataGrid
{
    public function prepareQueryBuilder()
    {
        return DB::table('discussions')
            ->leftJoin('boards', 'discussions.board_id', '=', 'boards.id')
            ->leftJoin('grades', 'discussions.grade_id', '=', 'grades.id')
            ->leftJoin('subjects', 'discussions.subject_id', '=', 'subjects.id')
            ->leftJoin('books', 'discussions.book_id', '=', 'books.id')
            ->leftJoin('chapters', 'discussions.chapter_id', '=', 'chapters.id')

            ->select(
                'discussions.id',
                'discussions.title',
                'discussions.creator_type',
                'discussions.creator_id',

                'discussions.board_id',
                'discussions.grade_id',
                'discussions.subject_id',
                'discussions.book_id',
                'discussions.chapter_id',

                'boards.name as board_name',
                'grades.name as grade_name',
                'subjects.name as subject_name',
                'books.title as book_title',
                'chapters.title as chapter_title',

                'discussions.status',
                'discussions.likes_count',
                'discussions.comments_count',
                'discussions.created_at'
            );
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
            'index'      => 'title',
            'label'      => 'Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'   => 'creator_type',
            'label'   => 'Created By',
            'type'    => 'string',
            'closure' => function ($row) {
                $type  = class_basename($row->creator_type);
                $badge = $type === 'Admin'
                    ? 'badge-primary'
                    : 'badge-info';
                return "<span class=\"badge badge-md {$badge}\">{$type} #{$row->creator_id}</span>";
            },
        ]);

        $this->addColumn([
            'index'      => 'chapter_title',
            'label'      => 'Chapter',
            'type'       => 'string',
            'closure'  => function ($row) {
                return $row->board_name .'->'. $row->grade_name .'->'. $row->subject_name .'->'. $row->book_title .'->'. $row->chapter_title;
            },
        ]);

        $this->addColumn([
            'index'   => 'likes_count',
            'label'   => 'Likes',
            'type'    => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'    => 'comments_count',
            'label'    => 'Comments',
            'type'     => 'integer',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => 'Status',
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_options' => [
                ['label' => 'Active',   'value' => 1],
                ['label' => 'Inactive', 'value' => 0],
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
            'icon'   => 'icon-view',
            'title'  => 'View',
            'method' => 'GET',
            'url'    => fn ($row) => route('admin.discussions.show', $row->id),
        ]);
        
        $this->addAction([
            'icon'   => 'icon-edit',
            'title'  => 'Edit',
            'method' => 'GET',
            'url'    => fn ($row) => route('admin.discussions.edit', $row->id),
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => 'Delete',
            'method' => 'DELETE',
            'url'    => fn ($row) => route('admin.discussions.delete', $row->id),
        ]);
    }
}