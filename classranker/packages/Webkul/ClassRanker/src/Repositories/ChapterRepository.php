<?php

namespace Webkul\ClassRanker\Repositories;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Storage;
use Webkul\ClassRanker\Contracts\Chapter;

class ChapterRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Chapter::class;
    }

    public function create(array $data)
    {
        $chapter = $this->model->create($data);

        $this->uploadImages(request()->all(), $chapter);

        return $chapter;
    }
    
    public function update(array $data, $id)
    {
        $chapter = $this->find($id);

        $chapter->update($data);

        $this->uploadImages(request()->all(), $chapter);

        return $chapter;
    }

    public function uploadImages($data, $chapter, $type = 'avatar')
    {
        if (isset($data[$type])) {
            foreach ($data[$type] as $imageId => $image) {
                $file = $type.'.'.$imageId;

                if (request()->hasFile($file)) {
                    if ($chapter->{$type}) {
                        Storage::delete($chapter->{$type});
                    }

                    $manager = new ImageManager;

                    $image = $manager->make(request()->file($file))->encode('webp');

                    $chapter->{$type} = 'chapter/'.$chapter->id.'/'.Str::random(40).'.webp';

                    Storage::put($chapter->{$type}, $image);

                    $chapter->save();
                }
            }
        } else {
            if ($chapter->{$type}) {
                Storage::delete($chapter->{$type});
            }

            $chapter->{$type} = null;

            $chapter->save();
        }
    }
}
