<?php

namespace CustomFeature\Question\DataGrids;

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
            ->join('question_assignments', 'questions.id', '=', 'question_assignments.question_id')
            ->leftJoin('boards',    'question_assignments.board_id',   '=', 'boards.id')
            ->leftJoin('grades',    'question_assignments.grade_id',   '=', 'grades.id')
            ->leftJoin('subjects',  'question_assignments.subject_id', '=', 'subjects.id')
            ->leftJoin('books',     'question_assignments.book_id',    '=', 'books.id')
            ->leftJoin('chapters',  'question_assignments.chapter_id', '=', 'chapters.id')
            ->addSelect(
                'question_assignments.id as assignment_id',
                'boards.name as board_name',
                'grades.name as grade_name',
                'subjects.name as subject_name',
                'books.title as book_title',
                'chapters.title as chapter_title',
                'questions.id as id',
                'questions.slug as slug',
                'questions.title as title',
                'questions.short_title as short_title',
                'questions.status as status',
                'questions.is_premium as is_premium',
                'questions.created_at as created_at',
                DB::raw('(SELECT COUNT(*) FROM question_items WHERE question_items.question_id = questions.id) as item_count'),
                DB::raw('(SELECT COUNT(*) FROM question_faqs  WHERE question_faqs.question_id  = questions.id) as faq_count'),
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
            'index'      => 'title',
            'label'      => 'Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'short_title',
            'label'      => 'Short Title',
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
            'index'      => 'board_name',
            'label'      => 'Board Name',
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
            'index'      => 'subject_name',
            'label'      => 'Subject Name',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'book_title',
            'label'      => 'Book Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'chapter_title',
            'label'      => 'Chapter Title',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);
        
        $this->addColumn([
            'index'      => 'item_count',
            'label'      => 'Question Count',
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'faq_count',
            'label'      => 'faq Count',
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
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
        ]);

        $this->addColumn([
            'index'      => 'is_premium',
            'label'      => 'Premium',
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
                return route('admin.study_materials.questions.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => 'Delete',
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.study_materials.questions.delete', $row->id);
            },
        ]);
    }
}
