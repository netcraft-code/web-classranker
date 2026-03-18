<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial;

use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\StudyMaterialController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial\QuizResource;
use CustomFeature\Quiz\Repositories\QuizRepository;
use Illuminate\Http\Request;

class QuizController extends StudyMaterialController
{
    /**
     * Is resource authorized.
     */
    public function isAuthorized(): bool
    {
        return false;
    }

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return QuizRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return QuizResource::class;
    }

    public function allResources(Request $request)
    {
        $query = $this->getRepositoryInstance()->with([
            'questions',
        ])->scopeQuery(function ($query) use ($request) {
            $query = $query->where('status', true);

            if ($this->isAuthorized()) {
                $query = $query->where('customer_id', $this->resolveShopUser($request)->id);
            }

            // chapter_id ko alag extract karo — baaki normal filters
            $filters = $request->except([...$this->requestException, 'chapter_id']);

            foreach ($filters as $input => $value) {
                $query = $query->whereIn($input, array_map('trim', explode(',', $value)));
            }

            // chapter_id ka special handling — quizChapters relation se
            if ($chapterIds = $request->input('chapter_id')) {
                $ids = array_map('trim', explode(',', $chapterIds));
                $query = $query->whereHas('quizChapters', function ($q) use ($ids) {
                    $q->whereIn('chapter_id', $ids);
                });
            }

            if ($sort = $request->input('sort')) {
                $query = $query->orderBy($sort, $request->input('order') ?? 'desc');
            } else {
                $query = $query->orderBy('id', 'asc');
            }

            return $query;
        });

        if (is_null($request->input('pagination')) || $request->input('pagination')) {
            $results = $query->paginate($request->input('limit') ?? 10);
        } else {
            $results = $query->get();
        }

        return $this->getResourceCollection($results);
    }

    public function getResource(Request $request, $id)
    {
        $resourceClassName = $this->resource();

        $query = $this->getRepositoryInstance()
            ->with([
                'questions.options',
            ]);

        if ($this->isAuthorized()) {
            $query = $query->where('customer_id', $this->resolveShopUser($request)->id);
        }

        $resource = $query->findOrFail($id);
        
        return new $resourceClassName($resource);
    }
}
