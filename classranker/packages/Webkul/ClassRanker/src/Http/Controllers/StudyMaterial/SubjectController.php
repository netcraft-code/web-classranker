<?php

namespace Webkul\ClassRanker\Http\Controllers\StudyMaterial;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\ClassRanker\Repositories\BoardRepository;
use Webkul\ClassRanker\Repositories\SubjectRepository;
use Webkul\ClassRanker\DataGrids\StudyMaterial\SubjectDataGrid;

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
            'code'     => ['required', 'unique:subjects,code'],
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

        session()->flash('success', trans('class_ranker::app.study_materials.subjects.subjects.create-success'));

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
            'code' => ['required', 'unique:subjects,code,'.$id],
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

        session()->flash('success', trans('class_ranker::app.study_materials.subjects.subjects.update-success'));

        return redirect()->route('admin.study_materials.subjects.subjects.index');
    }
    
    public function delete(int $id): JsonResponse
    {
        try {
            Event::dispatch('study_materials.subjects.subjects.delete.before', $id);
            
            $this->subjectRepository->delete($id);

            Event::dispatch('study_materials.subjects.subjects.delete.after', $id);

            return new JsonResponse(['message' => trans('class_ranker::app.study_materials.subjects.subjects.delete-success')]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => trans('class_ranker::app.study_materials.subjects.subjects.no-resource')]);
        }
    }
    
    public function massDelete(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            Event::dispatch('study_materials.subjects.subjects.delete.before', $index);

            $this->subjectRepository->delete($index);

            Event::dispatch('study_materials.subjects.subjects.delete.after', $index);
        }

        return new JsonResponse([
            'message' => trans('class_ranker::app.study_materials.subjects.subjects.index.datagrid.mass-delete-success'),
        ], 200);
    }
}
