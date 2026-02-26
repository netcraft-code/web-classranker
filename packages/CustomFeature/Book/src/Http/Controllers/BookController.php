<?php

namespace CustomFeature\Book\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use CustomFeature\Book\Repositories\BookRepository;
use CustomFeature\Board\Repositories\BoardRepository;
use CustomFeature\Book\DataGrids\BookDataGrid;

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

        session()->flash('success', 'Book created successfully.');

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

        session()->flash('success', 'Book updated successfully');

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

            return new JsonResponse(['message' => 'Book deleted successfully.']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'There is some error in deletion.']);
        }
    }
}
