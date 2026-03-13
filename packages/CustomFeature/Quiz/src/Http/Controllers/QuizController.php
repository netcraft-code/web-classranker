<?php

namespace CustomFeature\Quiz\Http\Controllers;

use CustomFeature\Board\Repositories\BoardRepository;
use CustomFeature\Quiz\DataGrids\QuizDataGrid;
use CustomFeature\Quiz\Http\Requests\UpdateQuizRequest;
use CustomFeature\Quiz\Repositories\QuizRepository;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;

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

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        return view('class_ranker::study-material.quizzes.index', compact('boards'));
    }

    /**
     * Store a newly created quiz in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'                    => 'required|string|max:255',
            'slug'                     => 'nullable|string|max:255',
            'chapters'                 => 'required|array|min:1',
            'chapters.*.board_id'      => 'required|exists:boards,id',
            'chapters.*.grade_id'      => 'required|exists:grades,id',
            'chapters.*.subject_id'    => 'required|exists:subjects,id',
            'chapters.*.book_id'       => 'required|exists:books,id',
            'chapters.*.chapter_id'    => 'required|exists:chapters,id',
        ]);
        
        $data = $request->all();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $quiz = $this->quizRepository->create($data);

        return response()->json([
            'message'      => 'Quiz created successfully.',
            'redirect_url' => route('admin.study_materials.quizzes.edit', $quiz->id),
        ]);
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

    // ── CHAPTERS ───────────────────────────────────────────────────
    public function addChapter(Request $request, $id)
    {
        $request->validate([
            'board_id'   => 'required|exists:boards,id',
            'grade_id'   => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'book_id'    => 'required|exists:books,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $quiz    = $this->quizRepository->findOrFail($id);

        $chapter = $quiz->quizChapters()->create($request->only([
            'board_id', 'grade_id', 'subject_id', 'book_id', 'chapter_id',
        ]));

        $chapter->load(['board', 'grade', 'subject', 'book', 'chapter']);

        return response()->json([
            'message' => 'Chapter added.',
            'chapter' => [
                'id'          => $chapter->id,
                'boardId'     => $chapter->board_id,
                'gradeId'     => $chapter->grade_id,
                'subjectId'   => $chapter->subject_id,
                'bookId'      => $chapter->book_id,
                'chapterId'   => $chapter->chapter_id,
                'boardName'   => $chapter->board->name,
                'gradeName'   => $chapter->grade->name,
                'subjectName' => $chapter->subject->name,
                'bookName'    => $chapter->book->title,
                'chapterName' => $chapter->chapter->title,
            ],
        ]);
    }

    public function removeChapter($id, $chapterId)
    {
        $quiz = $this->quizRepository->findOrFail($id);

        $quiz->quizChapters()->findOrFail($chapterId)->delete();

        return response()->json(['message' => 'Chapter removed.']);
    }

    // ── QUESTIONS ──────────────────────────────────────────────────
    public function addQuestion(Request $request, $id)
    {
        $request->validate([
            'text'                         => 'required|string',
            'solution'                     => 'nullable|string',
            'options'                      => 'required|array|min:2',
            'options.*.text'               => 'required|string',
            'options.*.use_tinymce'        => 'nullable|boolean',
            'correct_options'              => 'nullable|array',
        ]);

        $quiz     = $this->quizRepository->findOrFail($id);

        $question = $quiz->questions()->create([
            'quiz_id'           => $quiz->id,
            'question_text'     => $request->text,
            'question_solution' => $request->solution,
            'question_order'    => $quiz->questions()->count(),
        ]);

        foreach ($request->options as $index => $option) {
            $isCorrect = in_array($index, $request->correct_options ?? []);
            $question->options()->create([
                'option_text'  => $option['text'],
                'is_correct'   => $isCorrect,
                'use_tinymce'  => $option['use_tinymce'] ?? 0,
                'option_order' => $index,
            ]);
        }

        $question->load('options');

        return response()->json([
            'message'  => 'Question added.',
            'question' => $this->formatQuestion($question),
        ]);
    }

    public function updateQuestion(Request $request, $id, $questionId)
    {
        $request->validate([
            'text'                  => 'required|string',
            'solution'              => 'nullable|string',
            'options'               => 'required|array|min:2',
            'options.*.text'        => 'required|string',
            'options.*.use_tinymce' => 'nullable|boolean',
            'correct_options'       => 'nullable|array',
        ]);

        $quiz     = $this->quizRepository->findOrFail($id);

        $question = $quiz->questions()->findOrFail($questionId);

        $question->update([
            'question_text'     => $request->text,
            'question_solution' => $request->solution,
        ]);

        // Delete old options + recreate
        $question->options()->delete();

        foreach ($request->options as $index => $option) {
            $isCorrect = in_array($index, $request->correct_options ?? []);

            $question->options()->create([
                'option_text'  => $option['text'],
                'is_correct'   => $isCorrect,
                'use_tinymce'  => $option['use_tinymce'] ?? 0,
                'option_order' => $index,
            ]);
        }

        $question->load('options');

        return response()->json([
            'message'  => 'Question updated.',
            'question' => $this->formatQuestion($question),
        ]);
    }

    public function removeQuestion($id, $questionId)
    {
        $quiz     = $this->quizRepository->findOrFail($id);

        $question = $quiz->questions()->findOrFail($questionId);
        
        $question->options()->delete();

        $question->delete();

        return response()->json(['message' => 'Question removed.']);
    }

    private function formatQuestion($question): array
    {
        return [
            'id'       => $question->id,
            'text'     => $question->question_text,
            'solution' => $question->question_solution,
            'options'  => $question->options->map(fn($opt) => [
                'id'         => $opt->id,
                'text'       => $opt->option_text,
                'is_correct' => (bool) $opt->is_correct,
                'use_tinymce'=> (bool) $opt->use_tinymce,
                'order'      => $opt->option_order,
            ])->toArray(),
            'correctOptions' => $question->options
                ->filter(fn($opt) => $opt->is_correct)
                ->pluck('option_order')
                ->map(fn($o) => (int) $o)
                ->values()
                ->toArray(),
        ];
    }

    // edit() mein formattedQuestions update karo — solution add karo
    public function edit(int $id): View
    {
        $quiz = $this->quizRepository->getWithRelations($id);

        if (!$quiz) abort(404);

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        $formattedChapters = $quiz->quizChapters->map(fn($qc) => [
            'id'          => $qc->id,           // ← AJAX ke liye zaruri
            'boardId'     => (string) $qc->board_id,
            'gradeId'     => (string) $qc->grade_id,
            'subjectId'   => (string) $qc->subject_id,
            'bookId'      => (string) $qc->book_id,
            'chapterId'   => (string) $qc->chapter_id,
            'boardName'   => $qc->board->name,
            'gradeName'   => $qc->grade->name,
            'subjectName' => $qc->subject->name,
            'bookName'    => $qc->book->title,
            'chapterName' => $qc->chapter->title,
        ])->toArray();

        $formattedQuestions = $quiz->questions->map(fn($q) => [
            'id'       => $q->id,               // ← AJAX ke liye zaruri
            'text'     => $q->question_text,
            'solution' => $q->question_solution,
            'options'  => $q->options->map(fn($opt) => [
                'text'       => $opt->option_text,
                'useTinymce' => (bool) $opt->use_tinymce,
            ])->toArray(),
            'correctOptions' => $q->options
                ->filter(fn($opt) => $opt->is_correct)
                ->pluck('option_order')
                ->map(fn($order) => (int) $order)
                ->values()
                ->toArray(),
        ])->toArray();

        return view('class_ranker::study-material.quizzes.edit', compact(
            'quiz', 'boards', 'formattedChapters', 'formattedQuestions'
        ));
    }
}
