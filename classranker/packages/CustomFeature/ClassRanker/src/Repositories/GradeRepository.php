<?php

namespace CustomFeature\ClassRanker\Repositories;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Storage;
use CustomFeature\ClassRanker\Contracts\Grade;

class GradeRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Grade::class;
    }

    public function create(array $data)
    {
        $grade = $this->model->create($data);

        $this->uploadImages(request()->all(), $grade);

        return $grade;
    }
    
    public function update(array $data, $id)
    {
        $grade = $this->find($id);

        $grade->update($data);

        $this->uploadImages(request()->all(), $grade);

        return $grade;
    }

    public function uploadImages($data, $grade, $type = 'avatar')
    {
        if (isset($data[$type])) {
            foreach ($data[$type] as $imageId => $image) {
                $file = $type.'.'.$imageId;

                if (request()->hasFile($file)) {
                    if ($grade->{$type}) {
                        Storage::delete($grade->{$type});
                    }

                    $manager = new ImageManager;

                    $image = $manager->make(request()->file($file))->encode('webp');

                    $grade->{$type} = 'grade/'.$grade->id.'/'.Str::random(40).'.webp';

                    Storage::put($grade->{$type}, $image);

                    $grade->save();
                }
            }
        } else {
            if ($grade->{$type}) {
                Storage::delete($grade->{$type});
            }

            $grade->{$type} = null;

            $grade->save();
        }
    }
}
