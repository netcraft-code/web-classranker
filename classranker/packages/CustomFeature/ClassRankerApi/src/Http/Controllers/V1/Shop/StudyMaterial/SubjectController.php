<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial;

use CustomFeature\Subject\Repositories\SubjectRepository;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\StudyMaterialController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial\SubjectResource;

class SubjectController extends StudyMaterialController
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
        return SubjectRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return SubjectResource::class;
    }
}
