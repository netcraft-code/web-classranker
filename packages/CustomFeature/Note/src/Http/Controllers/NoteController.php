<?php

namespace CustomFeature\Note\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use CustomFeature\Note\Repositories\NoteRepository;
use CustomFeature\Board\Repositories\BoardRepository;
use CustomFeature\Note\DataGrids\NoteDataGrid;

class NoteController extends Controller
{
    public function __construct(
        protected NoteRepository  $noteRepository,
        protected BoardRepository $boardRepository
    ) {}

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(NoteDataGrid::class)->process();
        }
        return view('class_ranker::study-material.notes.index');
    }

    public function create()
    {
        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();
        return view('class_ranker::study-material.notes.create', compact('boards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'                    => 'required|string|max:255',
            'short_title'              => 'required|string|max:255',
            'slug'                     => 'nullable|string|max:255',
            'top_description'          => 'nullable',
            'content'                  => 'required',
            'bottom_description'       => 'nullable',
            'meta_title'               => 'nullable|string|max:255',
            'meta_description'         => 'nullable|string',
            'meta_keywords'            => 'nullable|string',
            'status'                   => 'nullable|boolean',
            'is_premium'               => 'nullable|boolean',
            'assignments'              => 'required|array|min:1',
            'assignments.*.board_id'   => 'required|exists:boards,id',
            'assignments.*.grade_id'   => 'required|exists:grades,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.book_id'    => 'required|exists:books,id',
            'assignments.*.chapter_id' => 'required|exists:chapters,id',
        ]);

        $this->noteRepository->create($request->all());

        session()->flash('success', 'Note created successfully.');
        return redirect()->route('admin.study_materials.notes.index');
    }

    public function edit($id)
    {
        $note = $this->noteRepository->with([
            'assignments.board', 'assignments.grade',
            'assignments.subject', 'assignments.book', 'assignments.chapter',
        ])->findOrFail($id);

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        $formattedAssignments = $note->assignments->map(fn($a) => [
            'boardId'     => $a->board_id,
            'gradeId'     => $a->grade_id,
            'subjectId'   => $a->subject_id,
            'bookId'      => $a->book_id,
            'chapterId'   => $a->chapter_id,
            'boardName'   => $a->board->name,
            'gradeName'   => $a->grade->name,
            'subjectName' => $a->subject->name,
            'bookName'    => $a->book->title,
            'chapterName' => $a->chapter->title,
        ]);

        return view('class_ranker::study-material.notes.edit', compact(
            'note', 'boards', 'formattedAssignments'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'                    => 'required|string|max:255',
            'short_title'              => 'required|string|max:255',
            'slug'                     => 'nullable|string|max:255|unique:notes,slug,' . $id,
            'top_description'          => 'nullable',
            'content'                  => 'required',
            'bottom_description'       => 'nullable',
            'meta_title'               => 'nullable|string|max:255',
            'meta_description'         => 'nullable|string',
            'meta_keywords'            => 'nullable|string',
            'status'                   => 'nullable|boolean',
            'is_premium'               => 'nullable|boolean',
            'assignments'              => 'required|array|min:1',
            'assignments.*.board_id'   => 'required|exists:boards,id',
            'assignments.*.grade_id'   => 'required|exists:grades,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.book_id'    => 'required|exists:books,id',
            'assignments.*.chapter_id' => 'required|exists:chapters,id',
        ]);

        $this->noteRepository->update($request->all(), $id);

        session()->flash('success', 'Note updated successfully.');
        return redirect()->route('admin.study_materials.notes.index');
    }

    public function delete($id)
    {
        try {
            $this->noteRepository->delete($id);
            session()->flash('success', 'Note deleted successfully.');
            return response()->json(['message' => true], 200);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete note.');
            return response()->json(['message' => false], 500);
        }
    }
}