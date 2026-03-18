<?php

namespace CustomFeature\Quiz\DataGrids;

use Webkul\DataGrid\DataGrid;
use Illuminate\Support\Facades\DB;

class QuizDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('quizzes')
            ->join('quiz_chapters', 'quizzes.id', '=', 'quiz_chapters.quiz_id')
            ->leftJoin('boards',   'quiz_chapters.board_id',   '=', 'boards.id')
            ->leftJoin('grades',   'quiz_chapters.grade_id',   '=', 'grades.id')
            ->leftJoin('subjects', 'quiz_chapters.subject_id', '=', 'subjects.id')
            ->leftJoin('books',    'quiz_chapters.book_id',    '=', 'books.id')
            ->leftJoin('chapters', 'quiz_chapters.chapter_id', '=', 'chapters.id')
            ->addSelect(
                'quiz_chapters.id as assignment_id',
                'boards.name as board_name',
                'grades.name as grade_name',
                'subjects.name as subject_name',
                'books.title as book_title',
                'chapters.title as chapter_title',
                'quizzes.id as id',
                'quizzes.slug as slug',
                'quizzes.title as title',
                'quizzes.status as status',
                'quizzes.is_premium as is_premium',
                'quizzes.created_at as created_at',
                DB::raw('(SELECT COUNT(*) FROM quiz_questions WHERE quiz_questions.quiz_id = quizzes.id) as question_count'),
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
            'index'      => 'question_count',
            'label'      => 'quizzes Count',
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
            'title'  => trans('class_ranker::app.study_materials.quizzes.index.datagrid.edit'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.study_materials.quizzes.edit', $row->id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-delete',
            'title'  => trans('class_ranker::app.study_materials.quizzes.index.datagrid.delete'),
            'method' => 'DELETE',
            'url'    => function ($row) {
                return route('admin.study_materials.quizzes.delete', $row->id);
            },
        ]);
    }
}
