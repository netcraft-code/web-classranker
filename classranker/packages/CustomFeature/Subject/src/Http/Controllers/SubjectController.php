<?php

namespace CustomFeature\Subject\Http\Controllers;

use CustomFeature\Board\Repositories\BoardRepository;
use CustomFeature\Subject\Repositories\SubjectRepository;
use CustomFeature\Subject\DataGrids\SubjectDataGrid;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;

class SubjectController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected SubjectRepository $subjectRepository,
        protected BoardRepository $boardRepository
    ) {}
    
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(SubjectDataGrid::class)->process();
        }

        return view('class_ranker::study-material.subjects.subjects.index');
    }
    
    public function create()
    {
        $boards = $this->boardRepository->where('status', 1)->with('grades')->get();

        return view('class_ranker::study-material.subjects.subjects.create', compact('boards'));
    }
    
    public function store()
    {
        $this->validate(request(), [
            'code'     => 'required',
            'name'     => 'required',
            'board_id' => ['required', 'exists:boards,id'],
            'grade_id' => ['required', 'exists:grades,id'],
        ]);

        Event::dispatch('study_materials.subjects.subjects.create.before');

        $data = request()->only([
            'code',
            'name',
            'board_id',
            'grade_id',
            'status',
        ]);

        $subject = $this->subjectRepository->create($data);

        Event::dispatch('study_materials.subjects.subjects.create.after', $subject);

        session()->flash('success', 'Subject created successfully');

        return redirect()->route('admin.study_materials.subjects.subjects.index');
    }
    
    public function edit(int $id)
    {
        $subject = $this->subjectRepository->findOrFail($id);

        return view('class_ranker::study-material.subjects.subjects.edit', compact('subject'));
    }
    
    public function update(int $id)
    {
        $this->validate(request(), [
            'code' => 'required',
            'name' => 'required',
        ]);

        Event::dispatch('study_materials.subjects.subjects.update.before', $id);
        
        $data = request()->only([
            'code',
            'name',
            'status',
        ]);

        $subject = $this->subjectRepository->update($data, $id);

        Event::dispatch('study_materials.subjects.subjects.update.after', $subject);

        session()->flash('success', 'Subject updated successfully');

        return redirect()->route('admin.study_materials.subjects.subjects.index');
    }
    
    public function delete(int $id): JsonResponse
    {
        try {
            Event::dispatch('study_materials.subjects.subjects.delete.before', $id);
            
            $this->subjectRepository->delete($id);

            Event::dispatch('study_materials.subjects.subjects.delete.after', $id);

            return new JsonResponse(['message' => 'Subject deleted successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'There is some issue in deletion']);
        }
    }
}
