<?php

namespace CustomFeature\Board\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use CustomFeature\Board\DataGrids\BoardDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use CustomFeature\Board\Repositories\BoardRepository;

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
    
    public function store()
    {
        $this->validate(request(), [
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:255|unique:boards,code',
            'title'    => 'nullable|string|max:255',
            'status'   => 'nullable|boolean',
            'avatar'   => 'nullable|array',
            'avatar.*' => 'nullable|image|max:2048',
        ]);

        Event::dispatch('study_materials.subjects.boards.create.before');

        $data = request()->only([
            'code',
            'name',
            'title',
            'status',
        ]);

        $board = $this->boardRepository->create($data);

        Event::dispatch('study_materials.subjects.boards.create.after', $board);
        
        return response()->json(['message' => 'Board created successfully.']);
    }
    
    public function edit(int $id)
    {
        $board = $this->boardRepository->findOrFail($id);
        
        return response()->json([
            'id'         => $board->id,
            'name'       => $board->name,
            'code'       => $board->code,
            'title'      => $board->title,
            'status'     => $board->status,
            'avatar_url' => $board->avatar_url,
        ]);
    }
    
    public function update(int $id)
    {
        $this->validate(request(), [
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:255|unique:boards,code,' . $id,
            'title'    => 'nullable|string|max:255',
            'status'   => 'nullable|boolean',
            'avatar'   => 'nullable|array',
            'avatar.*' => 'nullable|image|max:2048',
        ]);

        Event::dispatch('study_materials.subjects.boards.update.before', $id);

        $data = request()->only([
            'code',
            'name',
            'title',
        ]);

        $data['status'] = request()->boolean('status');

        $page = $this->boardRepository->update($data, $id);

        Event::dispatch('study_materials.subjects.boards.update.after', $page);
        
        return response()->json(['message' => 'Board updated successfully.']);
    }

    public function delete(int $id): JsonResponse
    {
        try {
            Event::dispatch('study_materials.subjects.boards.delete.before', $id);

            $this->boardRepository->delete($id);

            Event::dispatch('study_materials.subjects.boards.delete.after', $id);
            
            return new JsonResponse(['message' => 'Board deleted successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'There is some issue in deletion.']);
        }
    }
}
