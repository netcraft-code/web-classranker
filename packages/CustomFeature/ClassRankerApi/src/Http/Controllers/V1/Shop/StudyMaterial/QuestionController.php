<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial;

use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\StudyMaterialController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial\QuestionResource;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial\QuestionItemResource;
use CustomFeature\Question\Repositories\QuestionRepository;
use CustomFeature\Question\Repositories\QuestionItemRepository;
use Illuminate\Http\Request;

class QuestionController extends StudyMaterialController
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
        return QuestionRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return QuestionResource::class;
    }

    public function allResources(Request $request)
    {
        $query = $this->getRepositoryInstance()->scopeQuery(function ($query) use ($request) {
            $query = $query->where('status', true);

            if ($this->isAuthorized()) {
                $query = $query->where('customer_id', $this->resolveShopUser($request)->id);
            }

            // chapter_id ko alag extract karo — baaki normal filters
            $filters = $request->except([...$this->requestException, 'chapter_id']);

            foreach ($filters as $input => $value) {
                $query = $query->whereIn($input, array_map('trim', explode(',', $value)));
            }

            // chapter_id ka special handling — assignments relation se
            if ($chapterIds = $request->input('chapter_id')) {
                $ids = array_map('trim', explode(',', $chapterIds));
                $query = $query->whereHas('assignments', function ($q) use ($ids) {
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

    public function getResourceItems(Request $request)
    {
        $questionItemRepository = app()->make(QuestionItemRepository::class);
        
        $query = $questionItemRepository->scopeQuery(function ($query) use ($request) {
            $customer = $this->resolveShopUser($request);

            if ($this->isAuthorized()) {
                $query = $query->where('customer_id', $customer->id);
            }
            
            foreach ($request->except($this->requestException) as $input => $value) {
                $query = $query->whereIn($input, array_map('trim', explode(',', $value)));
            }

            $query = $query->withExists(['bookmarks as is_bookmarked' => function ($q) use ($customer) {
                $q->where('customer_id', $customer->id)
                  ->where('grade_id', $customer->grade_id);
            }]);

            if ($sort = $request->input('sort')) {
                $query = $query->orderBy($sort, $request->input('order') ?? 'desc');
            } else {
                $query = $query->orderBy('order', 'asc');
            }

            return $query;
        });

        if (is_null($request->input('pagination')) || $request->input('pagination')) {
            $results = $query->paginate($request->input('limit') ?? 10);
        } else {
            $results = $query->get();
        }

        return QuestionItemResource::collection($results);
    }
}
