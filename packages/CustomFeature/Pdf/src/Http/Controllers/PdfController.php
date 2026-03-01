<?php
namespace CustomFeature\Pdf\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Controllers\Controller;
use CustomFeature\Pdf\Repositories\PdfRepository;
use CustomFeature\Board\Repositories\BoardRepository;
use CustomFeature\Pdf\DataGrids\PdfDataGrid;

class PdfController extends Controller
{
    public function __construct(
        protected PdfRepository   $pdfRepository,
        protected BoardRepository $boardRepository
    ) {}

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(PdfDataGrid::class)->process();
        }

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        return view('class_ranker::study-material.pdfs.index', compact('boards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'                      => 'required|string|max:255',
            'short_title'                => 'required|string|max:255',
            'slug'                       => 'nullable|string|max:255',

            'assignments'                => 'required|array|min:1',
            'assignments.*.board_id'     => 'required|exists:boards,id',
            'assignments.*.grade_id'     => 'required|exists:grades,id',
            'assignments.*.subject_id'   => 'required|exists:subjects,id',
            'assignments.*.book_id'      => 'required|exists:books,id',
            'assignments.*.chapter_id'   => 'required|exists:chapters,id',
        ]);

        $pdf = $this->pdfRepository->create($request->all());

        return response()->json([
            'message'      => 'PDF created successfully.',
            'redirect_url' => route('admin.study_materials.pdfs.edit', $pdf->id),
        ]);
    }

    public function edit($id)
    {
        $pdf = $this->pdfRepository->with([
            'assignments.board', 'assignments.grade',
            'assignments.subject', 'assignments.book',
            'assignments.chapter', 'pdfItems',
        ])->findOrFail($id);

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        $formattedAssignments = $pdf->assignments->map(fn($a) => [
            'id'          => $a->id,
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

        $formattedPdfItems = $pdf->pdfItems->map(fn($item) => [
            'item_id'      => $item->id,
            'title'        => $item->title,
            'pdf_path'     => $item->pdf_path,
            'pdf_url'      => $item->pdf_path
                               ? Storage::disk('public')->url($item->pdf_path)
                               : null,
            'position'     => $item->position,
            'status'       => $item->status,
            'is_new'       => false,
        ]);

        return view('class_ranker::study-material.pdfs.edit', compact(
            'pdf', 'boards', 'formattedAssignments', 'formattedPdfItems'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'                    => 'required|string|max:255',
            'short_title'              => 'required|string|max:255',
            'slug'                     => 'nullable|string|max:255',
            'top_description'          => 'nullable',
            'bottom_description'       => 'nullable',
            'meta_title'               => 'nullable|string|max:255',
            'meta_description'         => 'nullable|string',
            'meta_keywords'            => 'nullable|string',
            'status'                   => 'nullable|boolean',
            'is_premium'               => 'nullable|boolean',
        ]);

        $this->pdfRepository->update($request->all(), $id);

        session()->flash('success', 'PDF updated successfully.');

        return redirect()->route('admin.study_materials.pdfs.index');
    }

    public function delete($id)
    {
        try {
            $this->pdfRepository->delete($id);

            session()->flash('success', 'PDF deleted successfully.');

            return response()->json(['message' => true], 200);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete PDF.');

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

        $pdf        = $this->pdfRepository->findOrFail($id);

        $assignment = $pdf->assignments()->create($request->only([
            'board_id', 'grade_id', 'subject_id', 'book_id', 'chapter_id',
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
        $pdf = $this->pdfRepository->findOrFail($id);

        $pdf->assignments()->findOrFail($assignmentId)->delete();

        return response()->json(['message' => 'Assignment removed.']);
    }

    // ── PDF ITEMS ──────────────────────────────────────────────────
    public function addPdfItem(Request $request, $id)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'pdf_temp_path'=> 'required|string',
            'position'     => 'nullable|integer',
            'status'       => 'nullable|boolean',
        ]);

        $pdf     = $this->pdfRepository->findOrFail($id);

        $pdfPath = null;

        if ($request->pdf_temp_path) {
            $newPath = str_replace('temp/pdfs', "pdfs/files/{$id}", $request->pdf_temp_path);

            Storage::disk('public')->move($request->pdf_temp_path, $newPath);

            $pdfPath = $newPath;
        }

        $item = $pdf->pdfItems()->create([
            'title'    => $request->title,
            'pdf_path' => $pdfPath,
            'position' => $request->position ?? $pdf->pdfItems()->count(),
            'status'   => $request->boolean('status', true),
        ]);

        return response()->json([
            'message' => 'PDF item added.',
            'item'    => [
                'id'       => $item->id,
                'title'    => $item->title,
                'pdf_path' => $item->pdf_path,
                'pdf_url'  => $item->pdf_path ? Storage::disk('public')->url($item->pdf_path) : null,
                'pdf_name' => $item->pdf_path ? basename($item->pdf_path) : null,
                'position' => $item->position,
                'status'   => $item->status,
            ],
        ]);
    }

    public function updatePdfItem(Request $request, $id, $itemId)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'position'     => 'nullable|integer',
            'status'       => 'nullable|boolean',
            'pdf_temp_path'=> 'nullable|string',
            'delete_pdf'   => 'nullable|boolean',
        ]);

        $pdf     = $this->pdfRepository->findOrFail($id);

        $item    = $pdf->pdfItems()->findOrFail($itemId);

        $pdfPath = $item->pdf_path;

        if ($request->pdf_temp_path) {
            $newPath = str_replace('temp/pdfs', "pdfs/files/{$id}", $request->pdf_temp_path);

            Storage::disk('public')->move($request->pdf_temp_path, $newPath);

            if ($item->pdf_path) Storage::disk('public')->delete($item->pdf_path);

            $pdfPath = $newPath;
        } elseif ($request->boolean('delete_pdf')) {
            if ($item->pdf_path) Storage::disk('public')->delete($item->pdf_path);

            $pdfPath = null;
        }

        $item->update([
            'title'    => $request->title,
            'pdf_path' => $pdfPath,
            'position' => $request->position ?? $item->position,
            'status'   => $request->boolean('status', true),
        ]);

        return response()->json([
            'message' => 'PDF item updated.',
            'item'    => [
                'id'       => $item->id,
                'title'    => $item->title,
                'pdf_path' => $item->pdf_path,
                'pdf_url'  => $item->pdf_path_url,
                'pdf_name' => $item->pdf_path ? basename($item->pdf_path) : null,
                'position' => $item->position,
                'status'   => $item->status,
            ],
        ]);
    }

    public function removePdfItem($id, $itemId)
    {
        $pdf  = $this->pdfRepository->findOrFail($id);

        $item = $pdf->pdfItems()->findOrFail($itemId);

        if ($item->pdf_path) Storage::disk('public')->delete($item->pdf_path);

        $item->delete();

        return response()->json(['message' => 'PDF item removed.']);
    }
}