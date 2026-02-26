<?php

namespace CustomFeature\Question\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use CustomFeature\Board\Repositories\BoardRepository;
use CustomFeature\Question\Repositories\QuestionRepository;
use CustomFeature\Question\DataGrids\QuestionDataGrid;

class QuestionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected QuestionRepository $questionRepository,
        protected BoardRepository $boardRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(QuestionDataGrid::class)->process();
        }

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        return view('class_ranker::study-material.questions.index', compact('boards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title'              => 'required|string|max:255',
            'short_title'        => 'required|string|max:255',
            'slug'               => 'required|string|max:255|unique:questions,slug',
            
            // Assignments validation
            'assignments'              => 'required|array|min:1',
            'assignments.*.board_id'   => 'required|exists:boards,id',
            'assignments.*.grade_id'   => 'required|exists:grades,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.book_id'    => 'required|exists:books,id',
            'assignments.*.chapter_id' => 'required|exists:chapters,id',
        ]);
        
        $question = $this->questionRepository->create($validatedData);

        return response()->json([
            'message'      => 'Question created successfully.',
            'redirect_url' => route('admin.study_materials.questions.edit', $question->id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $question = $this->questionRepository->with([
            'assignments.board',
            'assignments.grade',
            'assignments.subject',
            'assignments.book',
            'assignments.chapter',
            'questionItems',
            'faqs'
        ])->findOrFail($id);

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        // Format assignments for Vue
        $formattedAssignments = $question->assignments->map(function($assignment) {
            return [
                'id'        => $assignment->id,
                'boardId'   => $assignment->board_id,
                'gradeId'   => $assignment->grade_id,
                'subjectId' => $assignment->subject_id,
                'bookId'    => $assignment->book_id,
                'chapterId' => $assignment->chapter_id,
                'boardName' => $assignment->board->name,
                'gradeName' => $assignment->grade->name,
                'subjectName' => $assignment->subject->name,
                'bookName'  => $assignment->book->title,
                'chapterName' => $assignment->chapter->title,
            ];
        });
        
        // Format question items
        $formattedQuestionItems = $question->questionItems->map(fn($item) => [
            'id'              => $item->id,
            'question_number' => $item->question_number,
            'question'        => $item->question,
            'answer'          => $item->answer,
            'page_number'     => $item->page_number,
        ]);
        
        // Format FAQs
        $formattedFaqs = $question->faqs->map(fn($faq) => [
            'id'       => $faq->id,
            'question' => $faq->question,
            'answer'   => $faq->answer,
        ]);

        return view('class_ranker::study-material.questions.edit', compact(
            'question', 
            'boards', 
            'formattedAssignments', 
            'formattedQuestionItems', 
            'formattedFaqs'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title'              => 'required|string|max:255',
            'short_title'        => 'required|string|max:255',
            'slug'               => 'nullable|string|max:255|unique:questions,slug,' . $id,
            'top_description'    => 'nullable',
            'bottom_description' => 'nullable',
            'related_links'      => 'nullable',
            'meta_title'         => 'nullable|string|max:255',
            'meta_description'   => 'nullable|string',
            'meta_keywords'      => 'nullable|string',
            'status'             => 'nullable|boolean',
            'is_premium'         => 'nullable|boolean',
        ]);

        $question = $this->questionRepository->update($validatedData, $id);

        session()->flash('success', 'Question updated successfully');

        return redirect()->route('admin.study_materials.questions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        try {
            $this->questionRepository->delete($id);

            session()->flash('success', 'Question deleted successfully');

            return response()->json(['message' => true], 200);
        } catch (\Exception $e) {
            session()->flash('error', 'There is some issue in deletion');

            return response()->json(['message' => false], 500);
        }
    }

    // ── ASSIGNMENTS ────────────────────────────────────────────────
    public function addAssignment(Request $request, $id)
    {
        $request->validate([
            'board_id'   => 'required|exists:boards,id',
            'grade_id'   => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'book_id'    => 'required|exists:books,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $question   = $this->questionRepository->findOrFail($id);

        $assignment = $question->assignments()->create($request->only([
            'board_id',
            'grade_id',
            'subject_id',
            'book_id',
            'chapter_id',
        ]));

        $assignment->load(['board', 'grade', 'subject', 'book', 'chapter']);

        return response()->json([
            'message'    => 'Assignment added.',
            'assignment' => [
                'id'          => $assignment->id,
                'boardId'     => $assignment->board_id,
                'gradeId'     => $assignment->grade_id,
                'subjectId'   => $assignment->subject_id,
                'bookId'      => $assignment->book_id,
                'chapterId'   => $assignment->chapter_id,
                'boardName'   => $assignment->board->name,
                'gradeName'   => $assignment->grade->name,
                'subjectName' => $assignment->subject->name,
                'bookName'    => $assignment->book->title,
                'chapterName' => $assignment->chapter->title,
            ],
        ]);
    }

    public function removeAssignment($id, $assignmentId)
    {
        $question = $this->questionRepository->findOrFail($id);

        $question->assignments()->findOrFail($assignmentId)->delete();

        return response()->json([
            'message' => 'Assignment removed.'
        ]);
    }

    // ── QUESTION ITEMS ─────────────────────────────────────────────
    public function addQuestionItem(Request $request, $id)
    {
        $request->validate([
            'question_number' => 'required|string',
            'question'        => 'required|string',
            'answer'          => 'required|string',
            'page_number'     => 'nullable|string',
        ]);

        $question = $this->questionRepository->findOrFail($id);

        $item = $question->questionItems()->create([
            'question_number' => $request->question_number,
            'question'        => $request->question,
            'answer'          => $request->answer,
            'page_number'     => $request->page_number,
            'order'           => $request->order ?? $question->questionItems()->count(),
        ]);

        return response()->json([
            'message' => 'Question item added.',
            'item' => $item
        ]);
    }

    public function updateQuestionItem(Request $request, $id, $itemId)
    {
        $request->validate([
            'question_number' => 'required|string',
            'question'        => 'required|string',
            'answer'          => 'required|string',
            'page_number'     => 'nullable|string',
        ]);

        $question = $this->questionRepository->findOrFail($id);

        $item = $question->questionItems()->findOrFail($itemId);

        $item->update([
            'question_number' => $request->question_number,
            'question'        => $request->question,
            'answer'          => $request->answer,
            'page_number'     => $request->page_number,
            'order'           => $request->order ?? $question->questionItems()->count(),
        ]);

        return response()->json(['message' => 'Question item updated.', 'item' => $item]);
    }

    public function removeQuestionItem($id, $itemId)
    {
        $question = $this->questionRepository->findOrFail($id);

        $question->questionItems()->findOrFail($itemId)->delete();

        return response()->json([
            'message' => 'Question item removed.'
        ]);
    }

    // ── FAQS ───────────────────────────────────────────────────────
    public function addFaq(Request $request, $id)
    {
        $request->validate([
            'question' => 'required|string',
            'answer'   => 'required|string',
        ]);

        $question = $this->questionRepository->findOrFail($id);

        $faq = $question->faqs()->create([
            'question' => $request->question,
            'answer'   => $request->answer,
            'order'    => $request->order ?? $question->faqs()->count(),
        ]);

        return response()->json([
            'message' => 'FAQ added.',
            'faq'     => $faq
        ]);
    }

    public function updateFaq(Request $request, $id, $faqId)
    {
        $request->validate([
            'question' => 'required|string',
            'answer'   => 'required|string',
        ]);

        $question = $this->questionRepository->findOrFail($id);

        $faq = $question->faqs()->findOrFail($faqId);

        $faq->update([
            'question' => $request->question,
            'answer'   => $request->answer,
            'order'    => $request->order ?? $question->faqs()->count(),
        ]);

        return response()->json([
            'message' => 'FAQ updated.',
            'faq' => $faq
        ]);
    }

    public function removeFaq($id, $faqId)
    {
        $question = $this->questionRepository->findOrFail($id);

        $question->faqs()->findOrFail($faqId)->delete();

        return response()->json([
            'message' => 'FAQ removed.'
        ]);
    }
}