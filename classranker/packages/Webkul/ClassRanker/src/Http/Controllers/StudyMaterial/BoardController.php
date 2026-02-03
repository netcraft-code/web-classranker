<?php

namespace Webkul\ClassRanker\Http\Controllers\StudyMaterial;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\ClassRanker\DataGrids\StudyMaterial\BoardDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\ClassRanker\Repositories\BoardRepository;

class BoardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected BoardRepository $boardRepository) {}
    
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(BoardDataGrid::class)->process();
        }

        return view('class_ranker::study-material.subjects.boards.index');
    }
    
    public function create()
    {
        return view('class_ranker::study-material.subjects.boards.create');
    }
    
    public function store()
    {
        $this->validate(request(), [
            'code' => ['required', 'unique:boards,code'],
            'name' => 'required',
        ]);

        Event::dispatch('study_materials.subjects.boards.create.before');

        $data = request()->only([
            'code',
            'name',
            'status',
        ]);

        $board = $this->boardRepository->create($data);

        Event::dispatch('study_materials.subjects.boards.create.after', $board);

        session()->flash('success', trans('class_ranker::app.study_materials.subjects.boards.create-success'));

        return redirect()->route('admin.study_materials.subjects.boards.index');
    }
    
    public function edit(int $id)
    {
        $board = $this->boardRepository->findOrFail($id);

        return view('class_ranker::study-material.subjects.boards.edit', compact('board'));
    }
    
    public function update(int $id)
    {
        $this->validate(request(), [
            'code' => ['required', 'unique:boards,code,'.$id],
            'name' => 'required',
        ]);

        Event::dispatch('study_materials.subjects.boards.update.before', $id);

        $data = request()->only([
            'code',
            'name',
            'status',
        ]);

        $page = $this->boardRepository->update($data, $id);

        Event::dispatch('study_materials.subjects.boards.update.after', $page);

        session()->flash('success', trans('class_ranker::app.study_materials.subjects.boards.update-success'));

        return redirect()->route('admin.study_materials.subjects.boards.index');
    }

    public function delete(int $id): JsonResponse
    {
        try {
            Event::dispatch('study_materials.subjects.boards.delete.before', $id);

            $this->boardRepository->delete($id);

            Event::dispatch('study_materials.subjects.boards.delete.after', $id);
            
            return new JsonResponse(['message' => trans('class_ranker::app.study_materials.subjects.boards.delete-success')]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => trans('class_ranker::app.study_materials.subjects.boards.no-resource')]);
        }
    }
    
    public function massDelete(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            Event::dispatch('study_materials.subjects.boards.delete.before', $index);

            $this->boardRepository->delete($index);

            Event::dispatch('study_materials.subjects.boards.delete.after', $index);
        }

        return new JsonResponse([
            'message' => trans('class_ranker::app.study_materials.subjects.boards.index.datagrid.mass-delete-success'),
        ], 200);
    }
}
