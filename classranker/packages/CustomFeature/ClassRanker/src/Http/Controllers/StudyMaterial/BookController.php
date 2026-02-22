<?php

namespace CustomFeature\ClassRanker\Http\Controllers\StudyMaterial;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use CustomFeature\ClassRanker\Repositories\BookRepository;
use CustomFeature\ClassRanker\Repositories\BoardRepository;
use CustomFeature\ClassRanker\DataGrids\StudyMaterial\BookDataGrid;

class BookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected BookRepository $bookRepository,
        protected BoardRepository $boardRepository
    ) {}
    
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(BookDataGrid::class)->process();
        }

        return view('class_ranker::study-material.books.index');
    }
    
    public function create()
    {
        $boards = $this->boardRepository->where('status', 1)->with(['grades', 'grades.subjects'])->get();

        return view('class_ranker::study-material.books.create', compact('boards'));
    }
    
    public function store()
    {
        $this->validate(request(), [
            'code'       => 'required',
            'title'      => 'required',
            'board_id'   => ['required', 'exists:boards,id'],
            'grade_id'   => ['required', 'exists:grades,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $data = request()->only([
            'code',
            'title',
            'writer',
            'publisher',
            'edition',
            'publication_year',
            'total_pages',
            'board_id',
            'grade_id',
            'subject_id',
            'status',
        ]);

        Event::dispatch('study_materials.books.create.before');

        $book = $this->bookRepository->create($data);

        Event::dispatch('study_materials.books.create.after', $book);

        session()->flash('success', trans('class_ranker::app.study_materials.books.create-success'));

        return redirect()->route('admin.study_materials.books.index');
    }

    /**
     * To edit a previously created CMS page.
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $book = $this->bookRepository->findOrFail($id);

        return view('class_ranker::study-material.books.edit', compact('book'));
    }

    /**
     * To update the previously created CMS page in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(int $id)
    {
        $this->validate(request(), [
            'code'  => 'required',
            'title' => 'required',
        ]);

        $data = request()->only([
            'code',
            'title',
            'writer',
            'publisher',
            'edition',
            'publication_year',
            'total_pages',
            'status',
        ]);

        Event::dispatch('study_materials.books.update.before', $id);

        $page = $this->bookRepository->update($data, $id);

        Event::dispatch('study_materials.books.update.after', $page);

        session()->flash('success', trans('class_ranker::app.study_materials.books.update-success'));

        return redirect()->route('admin.study_materials.books.index');
    }

    /**
     * To delete the previously create CMS page.
     */
    public function delete(int $id): JsonResponse
    {
        try {
            Event::dispatch('study_materials.books.delete.before', $id);

            $this->bookRepository->delete($id);

            Event::dispatch('study_materials.books.delete.after', $id);

            return new JsonResponse(['message' => trans('class_ranker::app.study_materials.books.delete-success')]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => trans('class_ranker::app.study_materials.books.no-resource')]);
        }
    }

    /**
     * To mass delete the CMS resource from storage.
     */
    public function massDelete(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            Event::dispatch('study_materials.books.delete.before', $index);

            $this->bookRepository->delete($index);

            Event::dispatch('study_materials.books.delete.after', $index);
        }

        return new JsonResponse([
            'message' => trans('class_ranker::app.study_materials.books.index.datagrid.mass-delete-success'),
        ], 200);
    }
}
