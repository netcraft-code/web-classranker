<?php

namespace CustomFeature\Book\Repositories;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Storage;
use CustomFeature\Book\Contracts\Book;

class BookRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Book::class;
    }

    public function create(array $data)
    {
        $book = $this->model->create($data);

        $this->uploadImages(request()->all(), $book);

        return $book;
    }
    
    public function update(array $data, $id)
    {
        $book = $this->find($id);

        $book->update($data);

        $this->uploadImages(request()->all(), $book);

        return $book;
    }

    public function uploadImages($data, $book, $type = 'avatar')
    {
        if (isset($data[$type])) {
            foreach ($data[$type] as $imageId => $image) {
                $file = $type.'.'.$imageId;

                if (request()->hasFile($file)) {
                    if ($book->{$type}) {
                        Storage::delete($book->{$type});
                    }

                    $manager = new ImageManager;

                    $image = $manager->make(request()->file($file))->encode('webp');

                    $book->{$type} = 'book/'.$book->id.'/'.Str::random(40).'.webp';

                    Storage::put($book->{$type}, $image);

                    $book->save();
                }
            }
        } else {
            if ($book->{$type}) {
                Storage::delete($book->{$type});
            }

            $book->{$type} = null;

            $book->save();
        }
    }
}
