<?php

namespace CustomFeature\ClassRanker\Repositories;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Storage;
use CustomFeature\ClassRanker\Contracts\Board;

class BoardRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Board::class;
    }

    public function create(array $data)
    {
        $board = $this->model->create($data);

        $this->uploadImages(request()->all(), $board);

        return $board;
    }
    
    public function update(array $data, $id)
    {
        $board = $this->find($id);

        $board->update($data);

        $this->uploadImages(request()->all(), $board);

        return $board;
    }

    public function uploadImages($data, $board, $type = 'avatar')
    {
        if (isset($data[$type])) {
            foreach ($data[$type] as $imageId => $image) {
                $file = $type.'.'.$imageId;

                if (request()->hasFile($file)) {
                    if ($board->{$type}) {
                        Storage::delete($board->{$type});
                    }

                    $manager = new ImageManager;

                    $image = $manager->make(request()->file($file))->encode('webp');

                    $board->{$type} = 'board/'.$board->id.'/'.Str::random(40).'.webp';

                    Storage::put($board->{$type}, $image);

                    $board->save();
                }
            }
        } else {
            if ($board->{$type}) {
                Storage::delete($board->{$type});
            }

            $board->{$type} = null;

            $board->save();
        }
    }
}
