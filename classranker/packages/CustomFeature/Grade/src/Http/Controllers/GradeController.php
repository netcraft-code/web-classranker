<?php

namespace CustomFeature\Grade\Http\Controllers;

use CustomFeature\Board\Repositories\BoardRepository;
use CustomFeature\Grade\DataGrids\GradeDataGrid;
use CustomFeature\Grade\Repositories\GradeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;

class GradeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected GradeRepository $gradeRepository,
        protected BoardRepository $boardRepository
    ) {}
    
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(GradeDataGrid::class)->process();
        }

        return view('class_ranker::study-material.subjects.grades.index');
    }
    
    public function create()
    {
        $boards = $this->boardRepository->where('status', 1)->get();

        return view('class_ranker::study-material.subjects.grades.create', compact('boards'));
    }
    
    public function store()
    {
        $this->validate(request(), [
            'code'     => 'required',
            'name'     => 'required',
            'title'     => 'required',
            'board_id' => ['required', 'exists:boards,id'],
        ]);
        
        Event::dispatch('study_materials.subjects.grades.create.before');

        $data = request()->only([
            'code',
            'name',
            'title',
            'board_id',
            'status',
        ]);

        $grade = $this->gradeRepository->create($data);

        Event::dispatch('study_materials.subjects.grades.create.after', $grade);

        session()->flash('success', 'Grade created successfully.');

        return redirect()->route('admin.study_materials.subjects.grades.index');
    }
    
    public function edit(int $id)
    {
        $grade = $this->gradeRepository->findOrFail($id);

        return view('class_ranker::study-material.subjects.grades.edit', compact('grade'));
    }
    
    public function update(int $id)
    {
        $this->validate(request(), [
            'code' => 'required',
            'name' => 'required',
            'title' => 'required',
        ]);

        Event::dispatch('study_materials.subjects.grades.update.before', $id);

        $data = request()->only([
            'code',
            'name',
            'title',
            'status',
        ]);

        $page = $this->gradeRepository->update($data, $id);

        Event::dispatch('study_materials.subjects.grades.update.after', $page);

        session()->flash('success', 'Grade updated successfully.');

        return redirect()->route('admin.study_materials.subjects.grades.index');
    }
    
    public function delete(int $id): JsonResponse
    {
        try {
            Event::dispatch('study_materials.subjects.grades.delete.before', $id);

            $this->gradeRepository->delete($id);

            Event::dispatch('study_materials.subjects.grades.delete.after', $id);
            
            return new JsonResponse(['message' => 'Grade deleted successfully.']);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'There is some issue in deletion.']);
        }
    }
}
