<?php

namespace Webkul\ClassRanker\Http\Controllers\StudyMaterial;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\ClassRanker\Repositories\BoardRepository;
use Webkul\ClassRanker\Repositories\QuestionRepository;
use Webkul\ClassRanker\DataGrids\StudyMaterial\QuestionDataGrid;

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

        return view('class_ranker::study-material.questions.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Load boards with nested relationships
        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        return view('class_ranker::study-material.questions.create', compact('boards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'short_title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:questions,slug',
            'top_description' => 'nullable',
            'bottom_description' => 'nullable',
            'related_links' => 'nullable',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'status' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            
            // Assignments validation
            'assignments' => 'required|array|min:1',
            'assignments.*.board_id' => 'required|exists:boards,id',
            'assignments.*.grade_id' => 'required|exists:grades,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.book_id' => 'required|exists:books,id',
            'assignments.*.chapter_id' => 'required|exists:chapters,id',
            
            // Question items validation
            'question_items' => 'nullable|array',
            'question_items.*.question_number' => 'required|integer',
            'question_items.*.question_title' => 'nullable|string',
            'question_items.*.question' => 'required',
            'question_items.*.answer' => 'required',
            'question_items.*.page_number' => 'nullable|string',
            'question_items.*.order' => 'nullable|integer',
            
            // FAQs validation
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required|string',
            'faqs.*.answer' => 'required',
            'faqs.*.order' => 'nullable|integer',
        ]);

        $question = $this->questionRepository->create($validatedData);

        session()->flash('success', trans('class_ranker::app.study_materials.questions.create-success'));

        return redirect()->route('admin.study_materials.questions.index');
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

        return view('class_ranker::study-material.questions.edit', compact('question', 'boards'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'short_title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:questions,slug,' . $id,
            'top_description' => 'nullable',
            'bottom_description' => 'nullable',
            'related_links' => 'nullable',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'status' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
            
            // Assignments validation
            'assignments' => 'required|array|min:1',
            'assignments.*.board_id' => 'required|exists:boards,id',
            'assignments.*.grade_id' => 'required|exists:grades,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.book_id' => 'required|exists:books,id',
            'assignments.*.chapter_id' => 'required|exists:chapters,id',
            
            // Question items validation
            'question_items' => 'nullable|array',
            'question_items.*.question_number' => 'required|integer',
            'question_items.*.question_title' => 'nullable|string',
            'question_items.*.question' => 'required',
            'question_items.*.answer' => 'required',
            'question_items.*.page_number' => 'nullable|string',
            'question_items.*.order' => 'nullable|integer',
            
            // FAQs validation
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required|string',
            'faqs.*.answer' => 'required',
            'faqs.*.order' => 'nullable|integer',
        ]);

        $question = $this->questionRepository->update($validatedData, $id);

        session()->flash('success', trans('class_ranker::app.study_materials.questions.update-success'));

        return redirect()->route('admin.study_materials.questions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->questionRepository->delete($id);

            session()->flash('success', trans('class_ranker::app.study_materials.questions.delete-success'));

            return response()->json(['message' => true], 200);
        } catch (\Exception $e) {
            session()->flash('error', trans('class_ranker::app.study_materials.questions.delete-error'));

            return response()->json(['message' => false], 500);
        }
    }
}