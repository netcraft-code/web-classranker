<?php

namespace CustomFeature\Subject\Repositories;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Storage;
use CustomFeature\Subject\Contracts\Subject;

class SubjectRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Subject::class;
    }

    public function create(array $data)
    {
        $subject = $this->model->create($data);

        $this->uploadImages(request()->all(), $subject);

        return $subject;
    }
    
    public function update(array $data, $id)
    {
        $subject = $this->find($id);

        $subject->update($data);

        $this->uploadImages(request()->all(), $subject);

        return $subject;
    }

    public function uploadImages($data, $subject, $type = 'avatar')
    {
        if (isset($data[$type])) {
            foreach ($data[$type] as $imageId => $image) {
                $file = $type.'.'.$imageId;

                if (request()->hasFile($file)) {
                    if ($subject->{$type}) {
                        Storage::delete($subject->{$type});
                    }

                    $manager = new ImageManager;

                    $image = $manager->make(request()->file($file))->encode('webp');

                    $subject->{$type} = 'subject/'.$subject->id.'/'.Str::random(40).'.webp';

                    Storage::put($subject->{$type}, $image);

                    $subject->save();
                }
            }
        } else {
            if ($subject->{$type}) {
                Storage::delete($subject->{$type});
            }

            $subject->{$type} = null;

            $subject->save();
        }
    }
}
