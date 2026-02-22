<?php

namespace CustomFeature\ClassRanker\Http\Controllers\StudyMaterial;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use CustomFeature\ClassRanker\Repositories\BoardRepository;
use CustomFeature\ClassRanker\Repositories\ChapterRepository;
use CustomFeature\ClassRanker\DataGrids\StudyMaterial\ChapterDataGrid;

class ChapterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ChapterRepository $chapterRepository,
        protected BoardRepository $boardRepository
    ) {}
    
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ChapterDataGrid::class)->process();
        }

        return view('class_ranker::study-material.chapters.index');
    }
    
    public function create()
    {
        $boards = $this->boardRepository->where('status', 1)->with(['grades', 'grades.subjects', 'grades.subjects.books'])->get();
    
        return view('class_ranker::study-material.chapters.create', compact('boards'));
    }
    
    public function store()
    {
        $this->validate(request(), [
            'code'       => 'required',
            'title'      => 'required',
            'board_id'   => ['required', 'exists:boards,id'],
            'grade_id'   => ['required', 'exists:grades,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'book_id'    => ['required', 'exists:books,id'],
        ]);

        $data = request()->only([
            'code',
            'title',
            'board_id',
            'grade_id',
            'subject_id',
            'book_id',
        ]);

        Event::dispatch('study_materials.chapters.create.before');

        $chapter = $this->chapterRepository->create($data);

        Event::dispatch('study_materials.chapters.create.after', $chapter);

        session()->flash('success', trans('class_ranker::app.study_materials.chapters.create-success'));

        return redirect()->route('admin.study_materials.chapters.index');
    }

    /**
     * To edit a previously created CMS page.
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $chapter = $this->chapterRepository->findOrFail($id);

        return view('class_ranker::study-material.chapters.edit', compact('chapter'));
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
            'status',
        ]);

        Event::dispatch('study_materials.chapters.update.before', $id);

        $chapter = $this->chapterRepository->update($data, $id);

        Event::dispatch('study_materials.chapters.update.after', $chapter);

        session()->flash('success', trans('class_ranker::app.study_materials.chapters.update-success'));

        return redirect()->route('admin.study_materials.chapters.index');
    }

    /**
     * To delete the previously create CMS page.
     */
    public function delete(int $id): JsonResponse
    {
        try {
            Event::dispatch('study_materials.chapters.delete.before', $id);

            $this->chapterRepository->delete($id);

            Event::dispatch('study_materials.chapters.delete.after', $id);
            
            return new JsonResponse(['message' => trans('class_ranker::app.study_materials.chapters.delete-success')]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => trans('class_ranker::app.study_materials.chapters.no-resource')]);
        }
    }

    /**
     * To mass delete the CMS resource from storage.
     */
    public function massDelete(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            Event::dispatch('study_materials.chapters.delete.before', $index);

            $this->chapterRepository->delete($index);

            Event::dispatch('study_materials.chapters.delete.after', $index);
        }

        return new JsonResponse([
            'message' => trans('class_ranker::app.study_materials.chapters.index.datagrid.mass-delete-success'),
        ], 200);
    }
}
