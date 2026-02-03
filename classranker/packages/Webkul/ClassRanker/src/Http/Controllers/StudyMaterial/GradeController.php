<?php

namespace Webkul\ClassRanker\Http\Controllers\StudyMaterial;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\ClassRanker\Repositories\BoardRepository;
use Webkul\ClassRanker\Repositories\GradeRepository;
use Webkul\ClassRanker\DataGrids\StudyMaterial\GradeDataGrid;

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
            'code'     => ['required', 'unique:grades,code'],
            'name'     => 'required',
            'board_id' => ['required', 'exists:boards,id'],
        ]);
        
        Event::dispatch('study_materials.subjects.grades.create.before');

        $data = request()->only([
            'code',
            'name',
            'board_id',
            'status',
        ]);

        $grade = $this->gradeRepository->create($data);

        Event::dispatch('study_materials.subjects.grades.create.after', $grade);

        session()->flash('success', trans('class_ranker::app.study_materials.subjects.grades.create-success'));

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
            'code' => ['required', 'unique:grades,code,'.$id],
            'name' => 'required',
        ]);

        Event::dispatch('study_materials.subjects.grades.update.before', $id);

        $data = request()->only([
            'code',
            'name',
            'status',
        ]);

        $page = $this->gradeRepository->update($data, $id);

        Event::dispatch('study_materials.subjects.grades.update.after', $page);

        session()->flash('success', trans('class_ranker::app.study_materials.subjects.grades.update-success'));

        return redirect()->route('admin.study_materials.subjects.grades.index');
    }
    
    public function delete(int $id): JsonResponse
    {
        try {
            Event::dispatch('study_materials.subjects.grades.delete.before', $id);

            $this->gradeRepository->delete($id);

            Event::dispatch('study_materials.subjects.grades.delete.after', $id);
            
            return new JsonResponse(['message' => trans('class_ranker::app.study_materials.subjects.grades.delete-success')]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => trans('class_ranker::app.study_materials.subjects.grades.no-resource')]);
        }
    }
    
    public function massDelete(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            Event::dispatch('study_materials.subjects.grades.delete.before', $index);

            $this->gradeRepository->delete($index);

            Event::dispatch('study_materials.subjects.grades.delete.after', $index);
        }

        return new JsonResponse([
            'message' => trans('class_ranker::app.study_materials.subjects.grades.index.datagrid.mass-delete-success'),
        ], 200);
    }
}
