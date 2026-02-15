<?php

namespace Webkul\ClassRanker\Http\Controllers\StudyMaterial;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\ClassRanker\DataGrids\StudyMaterial\QuizDataGrid;
use Webkul\ClassRanker\Http\Requests\StoreQuizRequest;
use Webkul\ClassRanker\Http\Requests\UpdateQuizRequest;
use Webkul\ClassRanker\Repositories\BoardRepository;
use Webkul\ClassRanker\Repositories\QuizRepository;

class QuizController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected QuizRepository $quizRepository,
        protected BoardRepository $boardRepository
    ) {}
    
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(QuizDataGrid::class)->process();
        }

        return view('class_ranker::study-material.quizzes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        return view('class_ranker::study-material.quizzes.create', compact('boards'));
    }

    /**
     * Store a newly created quiz in storage
     */
    public function store(StoreQuizRequest $request): RedirectResponse
    {
        try {
            $this->quizRepository->create($request->validated());

            return redirect()
                ->route('admin.study_materials.quizzes.index')
                ->with('success', 'Quiz created successfully!');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating quiz: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified quiz
     */
    public function show(int $id): View
    {
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            abort(404, 'Quiz not found');
        }

        return view('class_ranker::study-material.quizzes.show', compact('quiz'));
    }

    /**
     * Show the form for editing the specified quiz
     */
    public function edit(int $id): View
    {
        $quiz = $this->quizRepository->getWithRelations($id);

        if (!$quiz) {
            abort(404, 'Quiz not found');
        }

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        // 🔥 PROPERLY FORMAT DATA (like Code 1)
        $formattedChapters = $quiz->quizChapters->map(function($qc) {
            return [
                'boardId' => (string) $qc->board_id,
                'gradeId' => (string) $qc->grade_id,
                'subjectId' => (string) $qc->subject_id,
                'bookId' => (string) $qc->book_id,
                'chapterId' => (string) $qc->chapter_id,
                'boardName' => $qc->board->name,
                'gradeName' => $qc->grade->name,
                'subjectName' => $qc->subject->name,
                'bookName' => $qc->book->title,
                'chapterName' => $qc->chapter->title,
            ];
        })->toArray();
        
        $formattedQuestions = $quiz->questions->map(function($q) {
            return [
                'text' => $q->question_text,
                'options' => $q->options->map(function($opt) {
                    return [
                        'text' => $opt->option_text,
                        'useTinymce' => (bool) $opt->use_tinymce,
                    ];
                })->toArray(),
                'correctOptions' => $q->options
                    ->filter(fn($opt) => $opt->is_correct)
                    ->pluck('option_order')
                    ->map(fn($order) => (int) $order)
                    ->values()
                    ->toArray(),
            ];
        })->toArray();

        return view('class_ranker::study-material.quizzes.edit', compact('quiz', 'boards', 'formattedChapters', 'formattedQuestions'));
    }

    /**
     * Update the specified quiz in storage
     */
    public function update(UpdateQuizRequest $request, $id): RedirectResponse
    {
        try {
            $this->quizRepository->update($request->validated(), $id);

            return redirect()
                ->route('admin.study_materials.quizzes.index')
                ->with('success', 'Quiz updated successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating quiz: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified quiz from storage
     */
    public function destroy($id)
    {
        try {
            $this->quizRepository->delete($id);

            session()->flash('success', trans('class_ranker::app.study_materials.quizzes.delete-success'));

            return response()->json(['message' => true], 200);
        } catch (\Exception $e) {
            session()->flash('error', trans('class_ranker::app.study_materials.quizzes.delete-error'));

            return response()->json(['message' => false], 500);
        }
    }
}
