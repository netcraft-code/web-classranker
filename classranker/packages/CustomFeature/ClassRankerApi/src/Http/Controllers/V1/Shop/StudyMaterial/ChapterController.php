<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial;

use CustomFeature\ClassRanker\Repositories\ChapterRepository;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\StudyMaterialController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial\ChapterResource;

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
}
