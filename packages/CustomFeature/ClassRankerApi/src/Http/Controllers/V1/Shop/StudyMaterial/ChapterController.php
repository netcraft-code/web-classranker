<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial;

use CustomFeature\Chapter\Repositories\ChapterRepository;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\StudyMaterialController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial\ChapterResource;
use Illuminate\Http\Request;

class ChapterController extends StudyMaterialController
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
        return ChapterRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return ChapterResource::class;
    }

    public function allResources(Request $request)
    {
        $query = $this->getRepositoryInstance()->scopeQuery(function ($query) use ($request) {
            if ($this->isAuthorized()) {
                $query = $query->where('customer_id', $this->resolveShopUser($request)->id);
            }

            foreach ($request->except($this->requestException) as $input => $value) {
                if ($input === 'title') {
                    $query = $query->where($input, 'LIKE', '%' . trim($value) . '%');
                } else {
                    $query = $query->whereIn($input, array_map('trim', explode(',', $value)));
                }
            }

            if ($sort = $request->input('sort')) {
                $query = $query->orderBy($sort, $request->input('order') ?? 'asc');
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
}
