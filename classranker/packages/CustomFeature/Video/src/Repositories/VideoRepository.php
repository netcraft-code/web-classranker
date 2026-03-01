<?php

namespace CustomFeature\Video\Repositories;

use CustomFeature\Video\Contracts\Video;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\Core\Eloquent\Repository;

class VideoRepository extends Repository
{
    public function model(): string
    {
        return Video::class;
    }

    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $video = $this->model->create([
                'title'       => $data['title'],
                'short_title' => $data['short_title'],
                'slug'        => $data['slug'],
            ]);

            // Assignments
            if (!empty($data['assignments'])) {
                foreach ($data['assignments'] as $assignment) {
                    $video->assignments()->create([
                        'board_id'   => $assignment['board_id'],
                        'grade_id'   => $assignment['grade_id'],
                        'subject_id' => $assignment['subject_id'],
                        'book_id'    => $assignment['book_id'],
                        'chapter_id' => $assignment['chapter_id'],
                    ]);
                }
            }

            DB::commit();

            return $video;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $video = $this->findOrFail($id);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $video->update([
                'title'              => $data['title'],
                'short_title'        => $data['short_title'],
                'slug'               => $data['slug'],
                'top_description'    => $data['top_description']    ?? null,
                'bottom_description' => $data['bottom_description'] ?? null,
                'meta_title'         => $data['meta_title']         ?? null,
                'meta_description'   => $data['meta_description']   ?? null,
                'meta_keywords'      => $data['meta_keywords']      ?? null,
                'status'             => $data['status']             ?? 0,
                'is_premium'         => $data['is_premium']         ?? 0,
            ]);

            DB::commit();

            return $video;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $video = $this->findOrFail($id);

            $video->assignments()->delete();
            $video->videoItems()->delete();
            $video->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}