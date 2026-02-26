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
        return view('class_ranker::study-material.pdfs.index');
    }

    public function create()
    {
        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();
        
        return view('class_ranker::study-material.pdfs.create', compact('boards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'                      => 'required|string|max:255',
            'short_title'                => 'required|string|max:255',
            'slug'                       => 'nullable|string|max:255|unique:pdfs,slug',
            'top_description'            => 'nullable',
            'bottom_description'         => 'nullable',
            'meta_title'                 => 'nullable|string|max:255',
            'meta_description'           => 'nullable|string',
            'meta_keywords'              => 'nullable|string',
            'status'                     => 'nullable|boolean',
            'is_premium'                 => 'nullable|boolean',
            'assignments'                => 'required|array|min:1',
            'assignments.*.board_id'     => 'required|exists:boards,id',
            'assignments.*.grade_id'     => 'required|exists:grades,id',
            'assignments.*.subject_id'   => 'required|exists:subjects,id',
            'assignments.*.book_id'      => 'required|exists:books,id',
            'assignments.*.chapter_id'   => 'required|exists:chapters,id',
            'pdf_items'                  => 'nullable|array',
            'pdf_items.*.title'          => 'required|string|max:255',
            'pdf_items.*.pdf_temp_path'  => 'required|string',
            'pdf_items.*.position'       => 'nullable|integer',
            'pdf_items.*.status'         => 'nullable|boolean',
        ]);

        $this->pdfRepository->create($request->all());

        session()->flash('success', 'PDF created successfully.');

        return redirect()->route('admin.study_materials.pdfs.index');
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
            'slug'                     => 'nullable|string|max:255|unique:pdfs,slug,' . $id,
            'top_description'          => 'nullable',
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
            'pdf_items'                => 'nullable|array',
            'pdf_items.*.title'        => 'required|string|max:255',
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
}