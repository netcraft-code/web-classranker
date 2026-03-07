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

    public function getByChapter(int $chapterId, int $limit = 6, int $page = 1): array
    {
        $query = $this->model
            ->whereHas('assignments', fn($q) =>
                $q->where('chapter_id', $chapterId)
            )
            ->where('status', 1)
            ->with(['videoItems' => fn($q) =>
                $q->where('status', 1)->orderBy('position')->limit(1)
            ]);

        $total = $query->count();
        $items = $query
            ->select('id', 'title', 'short_title', 'slug')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $formatted = $items->map(function ($video, $index) {
            $item = $video->videoItems->first();
            return [
                'id'            => $video->id,
                'title'         => $video->title,
                'short_title'   => $video->short_title,
                'slug'          => $video->slug,
                'index'         => $index,
                'thumbnail_url' => $item?->thumbnail_url,
                'video_url'     => $item?->video_link_url,
                'duration'      => $item?->duration,
                'video_item_id' => $item?->id,
            ];
        });

        return [
            'data'     => $formatted,
            'total'    => $total,
            'page'     => $page,
            'limit'    => $limit,
            'has_more' => ($page * $limit) < $total,
        ];
    }

    // Feed ke liye — ShortVideosScreen
    public function getFeed(
        ?int $chapterId = null,
        int $page = 1,
        int $limit = 10,
        ?int $seed = null
    ): array {
        $query = \CustomFeature\Video\Models\VideoItem::query()
            ->with('video:id,title,short_title,slug')
            ->whereHas('video', fn($q) => $q->where('status', 1))
            ->where('status', 1);

        if ($chapterId) {
            $query->whereHas('video.assignments', fn($q) =>
                $q->where('chapter_id', $chapterId)
            )
            ->orderBy('position');
        } else {
            $seed = $seed ?? rand(1, 9999);
            $query->orderByRaw("RAND({$seed})");
        }

        $total = $query->count();
        $items = $query
            ->select('id', 'video_id', 'title', 'video_link', 'thumbnail', 'duration')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        $formatted = $items->map(fn($item) => [
            'id'            => $item->id,
            'video_id'      => $item->video_id,
            'title'         => $item->title ?: $item->video?->title,
            'video_url'     => $item->video_link_url,
            'thumbnail_url' => $item->thumbnail_url,
            'duration'      => $item->duration,
        ]);

        return [
            'data'     => $formatted,
            'total'    => $total,
            'page'     => $page,
            'has_more' => ($page * $limit) < $total,
            'seed'     => $seed ?? null,
        ];
    }
}